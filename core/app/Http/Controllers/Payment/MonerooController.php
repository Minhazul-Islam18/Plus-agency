<?php

namespace App\Http\Controllers\Payment;

use App\BasicExtra;
use App\Http\Controllers\Payment\PaymentController;
use Illuminate\Http\Request;
use App\Language;
use App\Package;
use Illuminate\Support\Facades\Session;
use Moneroo\Laravel\Payment;

class MonerooController extends PaymentController
{
    public function store(Request $request)
    {
        // Get current language settings
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $bex = $currentLang->basic_extra;
        $package_inputs = $currentLang->package_inputs;

        // Validate order data
        $validation = $this->orderValidation($request, $package_inputs);
        if ($validation) {
            return $validation;
        }

        // Save order with pending status
        $po = $this->saveOrder($request, $package_inputs, 0);

        // Get package details
        $package = Package::find($request->package_id);
        $packageid = $package->id;

        // Calculate price with currency conversion
        $price = $package->price / $bex->base_currency_rate;
        $price = round($price, 2);

        try {
            // Initialize Moneroo payment
            $monerooPayment = new Payment();
            $payment = $monerooPayment->init([
                'amount' => $price,
                'currency' => 'USD',
                'customer' => [
                    'email' => $request->email,
                    'first_name' => $request->name,
                ],
                'return_url' => route('front.moneroo.notify'),
                'description' => 'Order Package: ' . $package->title,
            ]);

            // Store data in session
            Session::put('monerooPackageOrderId', $po->id);
            Session::put('monerooPackageId', $packageid);
            Session::put('monerooTransactionId', $payment->id);

            // Redirect to Moneroo checkout
            return redirect()->away($payment->checkout_url);

        } catch (\Exception $e) {
            return redirect()->back()->with('unsuccess', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    public function notify(Request $request)
    {
        // Get session data
        $orderId = Session::get('monerooPackageOrderId');
        $packageId = Session::get('monerooPackageId');
        $transactionId = Session::get('monerooTransactionId');

        if (!$transactionId || !$orderId) {
            return redirect()->route('front.payment.cancle', $packageId);
        }

        try {
            // Verify payment with Moneroo
            $monerooPayment = new Payment();
            $payment = $monerooPayment->get($transactionId);

            if ($payment->status === 'success' || $payment->status === 'completed') {
                // Get current language settings
                if (session()->has('lang')) {
                    $currentLang = Language::where('code', session()->get('lang'))->first();
                } else {
                    $currentLang = Language::where('is_default', 1)->first();
                }

                $be = $currentLang->basic_setting;
                $bex = $currentLang->basic_extra;

                // Update order status to completed
                $po = \App\PackageOrder::findOrFail($orderId);
                $po->payment_status = 1;
                $po->save();

                // Send confirmation emails
                $this->sendMails($po, $be, $bex);

                // Clear session
                Session::forget('monerooPackageOrderId');
                Session::forget('monerooPackageId');
                Session::forget('monerooTransactionId');

                return redirect()->route('front.packageorder.confirmation', [$packageId, $orderId])
                    ->with('success', 'Payment completed successfully!');
            } else {
                return redirect()->route('front.payment.cancle', $packageId);
            }

        } catch (\Exception $e) {
            return redirect()->route('front.payment.cancle', $packageId)
                ->with('unsuccess', 'Payment verification failed: ' . $e->getMessage());
        }
    }
}
