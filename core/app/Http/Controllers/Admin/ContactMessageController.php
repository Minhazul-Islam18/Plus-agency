<?php

namespace App\Http\Controllers\Admin;

use App\ContactMessage;
use App\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helpers\KreativMailer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ContactMessageController extends Controller
{
  public function index(Request $request)
  {
    $contactMessages = ContactMessage::when($request->status, function ($q) use ($request) {
        $q->where('status', $request->status);
      })
      ->orderBy('id', 'desc')
      ->paginate(10);

    return view('admin.contact_message.index', compact('contactMessages'));
  }

  public function delete(Request $request)
  {
    ContactMessage::findOrFail($request->contact_message_id)->delete();

    Session::flash('success', 'Message deleted successfully!');

    return redirect()->back();
  }

  public function bulkDelete(Request $request)
  {
    $ids = $request->ids;

    foreach ($ids as $id) {
      $cm = ContactMessage::findOrFail($id);
      $cm->delete();
    }

    Session::flash('success', 'Messages deleted successfully!');
    return "success";
  }

  public function approve(Request $request)
  {
    $contactMessage = ContactMessage::findOrFail($request->contact_message_id);
    $contactMessage->status = 'approved';
    $contactMessage->save();

    Session::flash('success', 'Message approved successfully!');

    return redirect()->back();
  }

  public function reject(Request $request)
  {
    $contactMessage = ContactMessage::findOrFail($request->contact_message_id);
    $contactMessage->status = 'rejected';
    $contactMessage->save();

    Session::flash('success', 'Message rejected successfully!');

    return redirect()->back();
  }

  public function reply(Request $request)
  {
    $request->validate([
      'contact_message_id' => 'required',
      'reply_subject' => 'required',
      'reply_message' => 'required',
    ]);

    $contactMessage = ContactMessage::findOrFail($request->contact_message_id);

    $currentLang = Language::where('is_default', 1)->first();
    $bs = $currentLang->basic_setting;

    $admin = Auth::guard('admin')->user();
    $adminName = trim($admin->first_name . ' ' . $admin->last_name);

    $mailer = new KreativMailer;

    $sent = $mailer->mailFromAdmin([
      'toMail'              => $contactMessage->email,
      'toName'              => $contactMessage->name,
      'customer_name'       => e($contactMessage->name),
      'contact_subject'     => e($contactMessage->subject),
      'contact_message'     => nl2br(e($contactMessage->message)),
      'admin_reply_message' => nl2br(e($request->reply_message)),
      'website_title'       => $bs->website_title,
      'templateType'        => 'contact_admin_reply',
      'type'                => 'contactAdminReply',
    ]);

    if ($sent) {
      $contactMessage->reply_message = $request->reply_message;
      $contactMessage->replied_at = now();
      $contactMessage->replied_by = $adminName;
      $contactMessage->save();

      Session::flash('success', 'Reply sent successfully!');
    } else {
      Log::error('[ContactMessage] Admin reply email failed', ['contact_message_id' => $contactMessage->id]);
      Session::flash('alert', 'Could not send the reply email. Check your SMTP settings and try again.');
    }

    return redirect()->back();
  }
}
