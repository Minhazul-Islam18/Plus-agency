<?php

namespace App\Http\Controllers\Payment\Tender;

use App\Http\Controllers\Controller;
use App\PaymentGateway;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class StripeController extends Controller
{
    use TenderPaymentHelper;

    public function __construct()
    {
        $data        = PaymentGateway::whereKeyword('stripe')->first();
        $conf        = json_decode($data->information, true);
        Config::set('services.stripe.key',    $conf['key']);
        Config::set('services.stripe.secret', $conf['secret']);
    }

    public function process(Request $request)
    {
        $lang  = $this->getLang();
        $bse   = $lang->basic_extra;

        $stripe = Stripe::make(Config::get('services.stripe.secret'));

        try {
            // Guard + build pending purchase first so we only charge the unpaid modules
            $purchase = $this->createPendingPurchase($request, 'stripe');

            // Server-authoritative amount = sum of the (unpaid) modules being bought
            $total = $this->tenderPayableAmount($purchase);
            if ($bse->base_currency_text !== 'USD') {
                $total = $total / max(1, (float) $bse->base_currency_rate);
            }

            $token = $stripe->tokens()->create([
                'card' => [
                    'number'    => $request->cardNumber,
                    'cvc'       => $request->cvcNumber,
                    'exp_month' => $request->month,
                    'exp_year'  => $request->year,
                ],
            ]);

            if (!isset($token['id'])) {
                return back()->with('error', 'Token creation failed.');
            }

            $charge = $stripe->charges()->create([
                'card'        => $token['id'],
                'currency'    => 'USD',
                'amount'      => $total,
                'description' => 'Tender Purchase',
            ]);

            if ($charge['status'] === 'succeeded') {
                $this->completePurchase($purchase->id, $charge['id'] ?? null);
                return $this->redirectToComplete($purchase);
            }
        } catch (\RuntimeException $e) {
            // Duplicate-payment guard (already-paid modules) — no pending
            // purchase was created here, so nothing to send a resume link for.
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            $this->handleFailedPayment($purchase ?? null);
            return back()->with('error', $e->getMessage());
        }

        $this->handleFailedPayment($purchase);
        return back()->with('error', 'Payment failed.');
    }
}
