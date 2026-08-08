<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtra;
use App\Http\Controllers\Controller;
use App\Guest;
use App\Notifications\PushDemo;
use App\PushNotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use NotificationChannels\WebPush\Events\NotificationFailed;
use NotificationChannels\WebPush\Events\NotificationSent;
use Notification;
use Validator;

class PushController extends Controller
{
    public function settings()
    {
        return view('admin.pushnotification.settings');
    }

    public function updateSettings(Request $request)
    {
        $icon = $request->icon;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
        $exticon = pathinfo($icon, PATHINFO_EXTENSION);

        $rules = [];

        if ($request->filled('icon')) {
            $rules['icon'] = [
                function ($attribute, $value, $fail) use ($exticon, $allowedExts) {
                    if (!in_array($exticon, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $request->validate($rules);

        if ($request->filled('icon')) {
            $bexs = BasicExtra::all();

            foreach ($bexs as $key => $bex) {
                @unlink(FRONT_IMG_PATH . $bex->push_notification_icon);
                $filename = uniqid() . '.' . $exticon;
                @copy($icon, FRONT_IMG_PATH . $filename);
                $bex->push_notification_icon = $filename;
                $bex->save();
            }
        }

        if ($request->has('public_key') && $request->has('private_key')) {
            $arr = ['VAPID_PUBLIC_KEY' => $request->public_key,'VAPID_PRIVATE_KEY' => $request->private_key];
            setEnvironmentValue($arr);
            \Artisan::call('config:clear');
        }

        session()->flash('success', 'Push Notification icon updated!');
        return back();
    }

    public function subscribers(Request $request)
    {
        $data['subscribers'] = Guest::orderBy('id', 'DESC')->paginate(15);
        return view('admin.pushnotification.subscribers', $data);
    }

    public function history(Request $request)
    {
        $data['logs'] = PushNotificationLog::orderBy('id', 'DESC')->paginate(10);
        return view('admin.pushnotification.history', $data);
    }

    public function statistics()
    {
        $data['total'] = Guest::count();
        $data['active'] = Guest::where('status', 1)->count();
        $data['inactive'] = Guest::where('status', 0)->count();
        $data['notificationsSent'] = PushNotificationLog::count();
        $data['totalRecipients'] = PushNotificationLog::sum('recipient_count');
        $data['totalOpened'] = PushNotificationLog::sum('opened_count');
        $data['totalFailed'] = PushNotificationLog::sum('failed_count');

        return view('admin.pushnotification.statistics', $data);
    }

    public function send()
    {
        $data['subscribers'] = Guest::where('status', 1)->orderBy('id', 'DESC')->get();
        return view('admin.pushnotification.send', $data);
    }

    public function push(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'button_url' => 'required',
            'button_text' => 'required',
            'notification_type' => 'required|in:general,personal',
            'subscriber_ids' => 'required_if:notification_type,personal|array',
        ]);

        $title = $request->title;
        $message = $request->message;
        $buttonText = $request->button_text;
        $buttonURL = $request->button_url;
        $type = $request->notification_type;

        if ($type === 'personal') {
            $guests = Guest::where('status', 1)->whereIn('id', $request->subscriber_ids ?? [])->get();
        } else {
            $guests = Guest::where('status', 1)->get();
        }

        if ($guests->isEmpty()) {
            $request->session()->flash('warning', 'No active subscribers to send to.');
            return redirect()->route('admin.pushnotification.send');
        }

        // Log row first so its id can be embedded in the click-tracking URL.
        $admin = Auth::guard('admin')->user();
        $log = PushNotificationLog::create([
            'title'              => $title,
            'message'            => $message,
            'button_text'        => $buttonText,
            'button_url'         => $buttonURL,
            'notification_type'  => $type,
            'recipient_count'    => 0,
            'failed_count'       => 0,
            'sent_by'            => $admin->id ?? null,
            'sent_by_name'       => $admin->username ?? null,
        ]);

        // The webpush package fires one of these per push-subscription send
        // attempt (it also auto-deletes the subscription itself when the
        // push service reports it as gone — we just mirror that onto the
        // owning Guest's status). Tally locally, scoped to this request.
        $sent = 0;
        $failed = 0;
        $failedGuestIds = [];

        $sentListener = function (NotificationSent $event) use (&$sent) {
            $sent++;
        };
        $failedListener = function (NotificationFailed $event) use (&$failed, &$failedGuestIds) {
            $failed++;
            $owner = $event->subscription->subscribable ?? null;
            if ($owner instanceof Guest) {
                $failedGuestIds[] = $owner->id;
            }
        };

        Event::listen(NotificationSent::class, $sentListener);
        Event::listen(NotificationFailed::class, $failedListener);

        Notification::send($guests, new PushDemo($title, $message, $buttonText, $buttonURL, $log->id));

        Event::forget(NotificationSent::class);
        Event::forget(NotificationFailed::class);

        if (!empty($failedGuestIds)) {
            Guest::whereIn('id', array_unique($failedGuestIds))->update(['status' => 0]);
        }

        $log->update(['recipient_count' => $sent, 'failed_count' => $failed]);

        $request->session()->flash('success', "Push notification sent to {$sent} subscriber(s)" . ($failed > 0 ? ", {$failed} failed" : '') . '.');
        return redirect()->route('admin.pushnotification.send');
    }
}
