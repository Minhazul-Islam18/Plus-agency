<?php

namespace App\Http\Controllers\Payment\Tender;

use App\Http\Controllers\Controller;
use App\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayController extends Controller
{
    use TenderPaymentHelper;

    private string $keyId;
    private string $keySecret;
    private Api $api;

    public function __construct()
    {
        $data          = PaymentGateway::whereKeyword('razorpay')->first();
        $info          = json_decode($data->information, true);
        $this->keyId   = $info['key'];
        $this->keySecret = $info['secret'];
        $this->api     = new Api($this->keyId, $this->keySecret);
    }

    public function redirect(Request $request)
    {
        $lang = $this->getLang();
        $bse  = $lang->basic_extra;

        if ($bse->base_currency_text !== 'INR') {
            return back()->with('error', __('Invalid Currency For Razorpay Payment.'));
        }

        try {
            $purchase = $this->createPendingPurchase($request, 'razorpay');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Server-authoritative amount = sum of the (unpaid) modules being bought
        $total = $this->tenderPayableAmount($purchase);

        $razorpayOrder = $this->api->order->create([
            'receipt'         => $purchase->order_number,
            'amount'          => $total * 100,
            'currency'        => 'INR',
            'payment_capture' => 1,
        ]);

        Session::put('tenderPurchaseId',    $purchase->id);
        Session::put('tenderRazorpayOrder', $razorpayOrder['id']);

        $notify_url = route('tender.razorpay.notify');

        $json = json_encode([
            'key'         => $this->keyId,
            'amount'      => $total,
            'name'        => 'Tender Purchase',
            'description' => 'Purchasing Tender Document via Razorpay',
            'prefill'     => [
                'name'    => $purchase->first_name . ' ' . $purchase->last_name,
                'email'   => $purchase->email,
                'contact' => $purchase->phone_number,
            ],
            'notes'       => ['order_number' => $purchase->order_number],
            'theme'       => ['color' => '#2563eb'],
            'order_id'    => $razorpayOrder['id'],
        ]);

        $version = $this->getVersion();

        return view('front.razorpay', compact('json', 'notify_url', 'version'));
    }

    public function notify(Request $request)
    {
        $id      = Session::get('tenderPurchaseId');
        $orderId = Session::get('tenderRazorpayOrder');
        $success = true;

        if (!empty($request->razorpay_payment_id)) {
            try {
                $this->api->utility->verifyPaymentSignature([
                    'razorpay_order_id'   => $orderId,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature'  => $request->razorpay_signature,
                ]);
            } catch (SignatureVerificationError $e) {
                $success = false;
            }
        } else {
            $success = false;
        }

        if ($success) {
            $purchase = $this->completePurchase($id, $request->razorpay_payment_id);
            Session::forget(['tenderPurchaseId', 'tenderRazorpayOrder']);
            return $this->redirectToComplete($purchase);
        }

        $this->handleFailedPayment($id ? \App\TenderPurchase::find($id) : null);
        Session::forget(['tenderPurchaseId', 'tenderRazorpayOrder']);

        return redirect()->route('tender.razorpay.cancel');
    }

    public function cancel()
    {
        return back()->with('error', 'Payment cancelled.');
    }
}
