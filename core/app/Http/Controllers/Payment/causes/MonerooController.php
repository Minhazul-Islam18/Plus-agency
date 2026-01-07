<?php

namespace App\Http\Controllers\Payment\causes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Support\Facades\Session;
use Moneroo\Laravel\Payment;

class MonerooController extends Controller
{
    public function paymentProcess(Request $request, $_amount, $_actual_amount, $_title, $_success_url, $_cancel_url)
    {
        $title = $_title;
        $price = $_amount;
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
                'return_url' => $_success_url,
                'description' => $title,
            ]);

            // Store data in session
            Session::put('monerooAmount', $_actual_amount);
            Session::put('monerooCauseData', $request->all());
            Session::put('monerooTransactionId', $payment->id);

            // Redirect to Moneroo checkout
            return redirect()->away($payment->checkout_url);

        } catch (\Exception $e) {
            return redirect()->to($_cancel_url)->with('unsuccess', 'Payment initialization failed: ' . $e->getMessage());
        }
    }
}
