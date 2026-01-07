<?php

namespace App\Http\Controllers\Payment\Course;

use App\Course;
use App\CoursePurchase;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Payment\Course\MailController;
use App\Language;
use App\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Moneroo\Laravel\Payment;
use PDF;

class MonerooGatewayController extends Controller
{
    public function redirectToMoneroo(Request $request)
    {
        $course = Course::findOrFail($request->course_id);

        if (!Auth::user()) {
            Session::put('link', route('course_details', ['slug' => $course->slug]));
            return redirect()->route('user.login');
        }

        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $bse = $currentLang->basic_extra;
        $total = $course->current_price;

        // Convert currency to USD if needed
        if ($bse->base_currency_text !== 'USD') {
            $base_rate = intval($bse->base_currency_rate);
            $total = $total / $base_rate;
        }

        $currency = $bse->base_currency_text;

        try {
            // Initialize Moneroo payment
            $monerooPayment = new Payment();
            $payment = $monerooPayment->init([
                'amount' => round($total, 2),
                'currency' => $currency,
                'customer' => [
                    'email' => Auth::user()->email,
                    'first_name' => Auth::user()->fname,
                    'last_name' => Auth::user()->lname,
                ],
                'return_url' => route('course.moneroo.notify'),
                'description' => 'Purchase Course: ' . $course->title,
            ]);

            // Store data in session
            Session::put('courseData', $course);
            Session::put('currency', $currency);
            Session::put('monerooTransactionId', $payment->id);

            // Redirect to Moneroo checkout
            return redirect()->away($payment->checkout_url);

        } catch (\Exception $e) {
            return redirect()->back()->with('unsuccess', 'Payment initialization failed: ' . $e->getMessage());
        }
    }

    public function notify(Request $request)
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $bs = $currentLang->basic_setting;
        $logo = $bs->logo;
        $bse = $currentLang->basic_extra;

        // Get session data
        $courseInfo = Session::get('courseData');
        $currency = Session::get('currency');
        $transactionId = Session::get('monerooTransactionId');

        if (!$transactionId) {
            return redirect()->route('course.moneroo.cancel');
        }

        try {
            // Verify payment with Moneroo
            $monerooPayment = new Payment();
            $payment = $monerooPayment->get($transactionId);

            if ($payment->status === 'success' || $payment->status === 'completed') {
                // Save course purchase
                $course_purchase = new CoursePurchase;
                $course_purchase->user_id = Auth::user()->id;
                $course_purchase->order_number = rand(100, 500) . time();
                $course_purchase->first_name = Auth::user()->fname;
                $course_purchase->last_name = Auth::user()->lname;
                $course_purchase->email = Auth::user()->email;
                $course_purchase->course_id = $courseInfo->id;
                $course_purchase->currency_code = $currency;
                $course_purchase->current_price = $courseInfo->current_price;
                $course_purchase->previous_price = $courseInfo->previous_price;
                $course_purchase->payment_method = 'moneroo';
                $course_purchase->payment_status = 'Completed';
                $course_purchase->save();

                // Generate PDF invoice
                $fileName = $course_purchase->order_number . '.pdf';
                $directory = 'assets/front/invoices/course/';
                @mkdir($directory, 0775, true);
                $fileLocated = $directory . $fileName;
                $order_info = $course_purchase;
                PDF::loadView('pdf.course', compact('order_info', 'logo', 'bse'))
                    ->setPaper('a4', 'landscape')->save($fileLocated);

                // Update invoice in database
                $course_purchase->update(['invoice' => $fileName]);

                // Send email
                MailController::sendMail($course_purchase);

                // Clear session
                Session::forget('courseData');
                Session::forget('currency');
                Session::forget('monerooTransactionId');

                return redirect()->route('course.moneroo.complete');
            } else {
                return redirect()->route('course.moneroo.cancel');
            }

        } catch (\Exception $e) {
            return redirect()->route('course.moneroo.cancel')->with('unsuccess', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    public function complete()
    {
        if (session()->has('lang')) {
            $currentLang = Language::where('code', session()->get('lang'))->first();
        } else {
            $currentLang = Language::where('is_default', 1)->first();
        }

        $be = $currentLang->basic_extended;
        $version = $be->theme_version;

        if ($version == 'dark') {
            $version = 'default';
        }

        $data['version'] = $version;
        return view('front.course.success', $data);
    }

    public function cancel()
    {
        return redirect()->back()->with('unsuccess', 'Payment Unsuccessful');
    }
}
