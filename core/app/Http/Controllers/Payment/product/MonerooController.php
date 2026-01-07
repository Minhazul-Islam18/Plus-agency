<?php

namespace App\Http\Controllers\Payment\product;

use App\Http\Controllers\Payment\product\PaymentController;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Moneroo\Laravel\Payment;
use PDF;

class MonerooController extends PaymentController
{
    public function store(Request $request)
    {
        if (!Session::has('cart')) {
            return view('errors.404');
        }

        $total = $this->orderTotal($request->shipping_charge);

        // Validate order data
        $this->orderValidation($request);

        // Get current language settings
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $bex = $currentLang->basic_extra;

        // Convert currency if needed
        $total = round(($total / $bex->base_currency_rate), 2);

        try {
            // Initialize Moneroo payment
            $monerooPayment = new Payment();
            $payment = $monerooPayment->init([
                'amount' => $total,
                'currency' => 'USD',
                'customer' => [
                    'email' => $request->billing_email,
                    'first_name' => $request->billing_fname,
                    'last_name' => $request->billing_lname,
                    'phone' => $request->billing_number,
                    'address' => $request->billing_address,
                    'city' => $request->billing_city,
                    'country' => $request->billing_country,
                ],
                'return_url' => route('product.moneroo.notify'),
                'description' => 'Product Order Payment',
            ]);

            // Store data in session
            Session::put('monerooOrderData', $request->all());
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
        $orderData = Session::get('monerooOrderData');
        $transactionId = Session::get('monerooTransactionId');

        if (!$transactionId || !$orderData) {
            return $this->paycancle();
        }

        try {
            // Verify payment with Moneroo
            $monerooPayment = new Payment();
            $payment = $monerooPayment->get($transactionId);

            if ($payment->status === 'success' || $payment->status === 'completed') {
                // Convert array to Request object
                $orderRequest = new Request($orderData);
                $orderRequest->setMethod('POST');

                // Save order to database
                $order = $this->saveOrder($orderRequest, $transactionId, $payment->id, 'Completed', 'online');

                // Save ordered items
                $this->saveOrderedItems($order->id);

                // Send confirmation emails
                $this->sendMails($order);

                // Clear session
                Session::forget('cart');
                Session::forget('coupon');
                Session::forget('monerooOrderData');
                Session::forget('monerooTransactionId');

                return $this->payreturn();
            } else {
                return $this->paycancle();
            }

        } catch (\Exception $e) {
            return redirect()->route('product.payment.cancle')->with('unsuccess', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    public function saveOrderedItems($orderId)
    {
        $cart = Session::get('cart');
        $products = [];

        foreach ($cart as $key => $item) {
            $product = \App\Product::findOrFail($key);

            $orderItem = new \App\OrderItem;
            $orderItem->product_order_id = $orderId;
            $orderItem->product_id = $product->id;
            $orderItem->title = $product->title;
            $orderItem->quantity = $item['qty'];
            $orderItem->price = round($product->current_price * $item['qty'], 2);
            $orderItem->previous_price = $product->previous_price;
            $orderItem->summary = $product->summary;
            $orderItem->save();
        }
    }

    public function sendMails($order)
    {
        // Get current language settings
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $bs = $currentLang->basic_setting;
        $logo = $bs->logo;
        $bex = $currentLang->basic_extra;

        // Generate PDF invoice
        $order_info = $order;
        $file_name = str_random(4) . time() . '.pdf';
        $pdf = PDF::loadView('pdf.product', compact('order_info', 'logo', 'bex'))->save('assets/front/invoices/' . $file_name);

        // Update order with invoice file name
        $order->invoice = $file_name;
        $order->save();

        // Send email to buyer
        $mailer = new \App\Http\Helpers\KreativMailer;
        $data = [
            'toMail' => $order->billing_email,
            'toName' => $order->billing_fname,
            'attachment' => $file_name,
            'templateType' => 'product_order',
            'type' => 'productOrder'
        ];

        $mailer->mailFromAdmin($data);
    }
}
