<?php

namespace App\Http\Controllers\Payment\Tender;

use App\Http\Controllers\Controller;
use App\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Moneroo\Laravel\Payment;

class MonerooController extends Controller
{
    use TenderPaymentHelper;

    public function __construct()
    {
        $data   = PaymentGateway::whereKeyword('moneroo')->first();
        $info   = json_decode($data->information, true);
        Config::set('moneroo.secretKey', $info['secret_key']);
    }

    public function redirect(Request $request)
    {
        $lang  = $this->getLang();
        $bse   = $lang->basic_extra;
        $total = (float) $request->selected_amount;

        if ($bse->base_currency_text !== 'USD') {
            $total = $total / max(1, (float) $bse->base_currency_rate);
        }

        $purchase = $this->createPendingPurchase($request, 'moneroo');

        try {
            $moneroo = new Payment();
            $payment = $moneroo->init([
                'amount'      => round($total, 2),
                'currency'    => $bse->base_currency_text,
                'customer'    => [
                    'email'      => $purchase->email,
                    'first_name' => $purchase->first_name,
                    'last_name'  => $purchase->last_name,
                ],
                'return_url'  => route('tender.moneroo.notify'),
                'description' => 'Tender Purchase: ' . $purchase->order_number,
            ]);

            Session::put('tenderPurchaseId',       $purchase->id);
            Session::put('tenderMonerooTransaction', $payment->id);

            return redirect()->away($payment->checkout_url);
        } catch (\Exception $e) {
            return back()->with('error', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    public function notify(Request $request)
    {
        $id            = Session::get('tenderPurchaseId');
        $transactionId = Session::get('tenderMonerooTransaction');

        if (!$transactionId) {
            return redirect()->route('tender.moneroo.cancel');
        }

        try {
            $moneroo = new Payment();
            $payment = $moneroo->get($transactionId);

            if (in_array($payment->status, ['success', 'completed'])) {
                $purchase = $this->completePurchase($id);
                Session::forget(['tenderPurchaseId', 'tenderMonerooTransaction']);
                return $this->redirectToComplete($purchase);
            }
        } catch (\Exception $e) {
            // fall through to cancel
        }

        return redirect()->route('tender.moneroo.cancel');
    }

    public function cancel()
    {
        return back()->with('error', 'Payment cancelled.');
    }
}
