<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\KreativMailer;
use App\BasicSetting;
use App\NewsletterLog;
use App\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Session;

class SubscriberController extends Controller
{
    public function index(Request $request) {
        $term = $request->term;

      $data['subscs'] = Subscriber::when($term, function ($query, $term) {
                            return $query->where('email', 'LIKE', '%' . $term . '%');
                        })->orderBy('id', 'DESC')->paginate(5);
      return view('admin.subscribers.index', $data);
    }

    public function mailsubscriber() {
      $data['subscribers'] = Subscriber::orderBy('id', 'DESC')->get();
      return view('admin.subscribers.mail', $data);
    }

    public function subscsendmail(Request $request) {
        if(Subscriber::count() == 0) {
            $request->session()->flash('warning', "No subscriber found!");
            return back();
        }

      $request->validate([
        'subject' => 'required',
        'message' => 'required',
        'recipient_type' => 'required|in:general,personal',
        'subscriber_ids' => 'required_if:recipient_type,personal|array',
      ]);

      $sub = $request->subject;
      $msg = clean($request->message);

      $subscs = $request->recipient_type == 'personal'
          ? Subscriber::whereIn('id', $request->subscriber_ids)->get()
          : Subscriber::all();

      if ($subscs->isEmpty()) {
          $request->session()->flash('warning', "No subscriber found!");
          return back();
      }

      $settings = BasicSetting::first();

      $mailer = new KreativMailer;
      $sent = 0;
      $failed = 0;

      foreach ($subscs as $subsc) {
          $ok = $mailer->mailFromAdmin([
              'toMail'              => $subsc->email,
              'toName'              => $subsc->email,
              'website_title'       => $settings->website_title,
              'newsletter_subject'  => $sub,
              'newsletter_content'  => $msg,
              'unsubscribe_link'    => route('front.unsubscribe.token', $subsc->unsubscribe_token),
              'templateType'        => 'newsletter',
          ]);

          if ($ok) {
              $sent++;
          } else {
              $failed++;
          }
      }

      $admin = Auth::guard('admin')->user();
      NewsletterLog::create([
          'subject'         => $sub,
          'message'         => $msg,
          'recipient_count' => $sent,
          'failed_count'    => $failed,
          'sent_by'         => $admin->id ?? null,
          'sent_by_name'    => $admin->username ?? null,
      ]);

      if ($failed > 0) {
          Session::flash('warning', "Newsletter sent to {$sent} subscriber(s), {$failed} failed — check the mail logs.");
      } else {
          Session::flash('success', "Newsletter sent to {$sent} subscriber(s) successfully!");
      }
      return back();
    }

    public function history(Request $request)
    {
        $data['logs'] = NewsletterLog::orderBy('id', 'DESC')->paginate(10);
        return view('admin.subscribers.history', $data);
    }

    public function delete(Request $request)
    {

        $subscriber = Subscriber::findOrFail($request->subscriber_id);
        $subscriber->delete();

        Session::flash('success', 'Subscriber deleted successfully!');
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $subscriber = Subscriber::findOrFail($id);
            $subscriber->delete();
        }

        Session::flash('success', 'Subscribers deleted successfully!');
        return "success";
    }
}
