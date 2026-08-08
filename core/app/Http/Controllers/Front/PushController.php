<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Guest;
use App\PushNotificationLog;

class PushController extends Controller
{
    /**
     * Click-through for a push notification's action button: counts the
     * open, then forwards to the real destination. Public (no auth) —
     * this is hit from an OS-level notification, not a logged-in session.
     */
    public function track(Request $request, $log)
    {
        $to = $request->query('to');

        PushNotificationLog::where('id', $log)->increment('opened_count');

        return redirect()->away($to ?: route('front.index'));
    }


    /**
     * Store the PushSubscription.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request){
        $this->validate($request,[
            'endpoint'    => 'required',
            'keys.auth'   => 'required',
            'keys.p256dh' => 'required'
        ]);
        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];
        $user = Guest::firstOrCreate([
            'endpoint' => $endpoint
        ]);
        [$device, $browser] = $this->parseUserAgent($request->userAgent());
        $user->device = $device;
        $user->browser = $browser;
        $user->status = 1;
        $user->save();
        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true],200);
    }

    /**
     * Coarse device/browser detection for the admin Subscribers list —
     * good enough for a display column, not analytics-grade. No package
     * dependency needed for this level of detail.
     */
    private function parseUserAgent(?string $ua): array
    {
        $ua = $ua ?? '';

        if (preg_match('/iPad/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/Mobi|Android(?!.*Tablet)|iPhone/i', $ua)) {
            $device = 'Mobile';
        } else {
            $device = 'Desktop';
        }

        if (preg_match('/Edg\//i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/OPR\/|Opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Firefox/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $ua)) {
            $browser = 'Safari';
        } else {
            $browser = 'Other';
        }

        return [$device, $browser];
    }
}
