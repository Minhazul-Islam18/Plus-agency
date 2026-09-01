<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\OtpVerification;
use App\TenderDeviceOtp;
use App\TenderDeviceRegistration;
use App\TenderPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TenderDeviceController extends Controller
{
    private function filtered(Request $request)
    {
        $q      = trim((string) $request->input('q', ''));
        $status = $request->input('status');

        // TenderDeviceRegistration.order_id is a plain string (the order
        // number), not a real FK to tender_purchases — resolve any
        // name/email match to order numbers first, then filter by those.
        $matchingOrders = $q !== ''
            ? TenderPurchase::where('email', 'like', "%{$q}%")
                ->orWhere('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")
                ->pluck('order_number')
            : collect();

        return TenderDeviceRegistration::query()
            ->when($q !== '', function ($query) use ($q, $matchingOrders) {
                $query->where(function ($sub) use ($q, $matchingOrders) {
                    $sub->where('device_label', 'like', "%{$q}%")
                        ->orWhere('order_id', 'like', "%{$q}%")
                        ->orWhereIn('order_id', $matchingOrders);
                });
            })
            ->when(in_array($status, ['pending', 'active', 'revoked'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByDesc('last_used_at');
    }

    public function index(Request $request)
    {
        $devices = $this->filtered($request)->paginate(10)->withQueryString();

        $stats = [
            'total'   => TenderDeviceRegistration::count(),
            'active'  => TenderDeviceRegistration::where('status', 'active')->count(),
            'pending' => TenderDeviceRegistration::where('status', 'pending')->count(),
            'revoked' => TenderDeviceRegistration::where('status', 'revoked')->count(),
        ];

        // Every purchase referenced on this page, keyed by order_number —
        // avoids an N+1 (TenderDeviceRegistration has no real FK relation to
        // TenderPurchase since it's keyed on order_id, a string).
        $orderNumbers = $devices->pluck('order_id')->unique();
        $purchases = TenderPurchase::whereIn('order_number', $orderNumbers)->get()->keyBy('order_number');

        return view('admin.tender.device.index', compact('devices', 'stats', 'purchases'));
    }

    /**
     * "Reset" — removes this device from the recognized list entirely. It
     * will need a fresh OTP round to access the order's link again. Distinct
     * from "Revoke" (blocks outright, no self-service way back in) and
     * "Delete" (also purges its access history).
     */
    public function reset(Request $request)
    {
        $device = TenderDeviceRegistration::findOrFail($request->device_id);
        $orderId = $device->order_id;
        $device->delete();

        \App\TenderAuditLog::record('device_reset', TenderPurchase::where('order_number', $orderId)->first(),
            'Recognized device reset', ['order' => $orderId, 'device_id' => $request->device_id]);

        Session::flash('success', 'Device reset. It will need a new email code to access this order again.');
        return back();
    }

    /**
     * "Revoke" — blocks this exact device outright. Unlike Reset, a revoked
     * device can't self-service its way back in via OTP; only an admin
     * resetting or deleting it clears the block.
     */
    public function revoke(Request $request)
    {
        $device = TenderDeviceRegistration::findOrFail($request->device_id);
        $device->update(['status' => 'revoked']);

        \App\TenderAuditLog::record('device_revoked', TenderPurchase::where('order_number', $device->order_id)->first(),
            'Device access revoked', ['order' => $device->order_id, 'device_id' => $device->id]);

        Session::flash('success', 'Device access revoked.');
        return back();
    }

    /**
     * "Delete" — permanently removes the device AND its access history.
     */
    public function destroy(Request $request)
    {
        $device = TenderDeviceRegistration::findOrFail($request->device_id);
        $orderId = $device->order_id;
        \App\TenderDeviceAccessLog::where('device_registration_id', $device->id)->delete();
        $device->delete();

        \App\TenderAuditLog::record('device_deleted', TenderPurchase::where('order_number', $orderId)->first(),
            'Device permanently deleted', ['order' => $orderId, 'device_id' => $request->device_id]);

        Session::flash('success', 'Device permanently deleted.');
        return back();
    }

    /**
     * Manually re-sends an OTP email for a device already sitting in
     * 'pending' status — same code path the buyer's own resend button uses
     * front-end, for when they say they never got the email. Called via
     * fetch() from the device list/details modal, which then opens the
     * inline "Enter Verification Code" modal (verifyOtp below) so the admin
     * can type back a code the customer reads out over phone/chat, without
     * the customer needing to click anything themself.
     */
    public function resendOtp(Request $request)
    {
        $device = TenderDeviceRegistration::findOrFail($request->device_id);
        $purchase = TenderPurchase::where('order_number', $device->order_id)->first();

        if (!$purchase) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Could not find the order for this device.']);
            }
            Session::flash('error', 'Could not find the order for this device.');
            return back();
        }

        TenderDeviceOtp::where('order_id', $device->order_id)
            ->where('device_hash', $device->device_hash)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        $rawOtp = OtpVerification::generateOtp();
        TenderDeviceOtp::create([
            'order_id'       => $device->order_id,
            'device_hash'    => $device->device_hash,
            'email_hash'     => hash('sha256', strtolower(trim($purchase->email))),
            'otp_hash'       => hash('sha256', $rawOtp),
            'attempts'       => 0,
            'expires_at'     => now()->addMinutes(TenderDeviceOtp::OTP_TTL_MIN),
            'last_resend_at' => now(),
            'status'         => 'pending',
            'ip'             => $request->ip(),
        ]);

        try {
            $mailer = new \App\Http\Helpers\KreativMailer;
            $mailer->mailFromAdmin([
                'toMail'          => $purchase->email,
                'toName'          => $purchase->first_name,
                'otp_code'        => $rawOtp,
                'otp_ttl_minutes' => TenderDeviceOtp::OTP_TTL_MIN,
                'website_title'   => optional(\App\Language::where('is_default', 1)->first())->basic_setting->website_title ?? config('app.name'),
                'templateType'    => 'tender_recovery_otp',
                'type'            => 'tenderRecoveryOtp',
            ]);
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Could not send the code: ' . $e->getMessage()]);
            }
            Session::flash('error', 'Could not send the code: ' . $e->getMessage());
            return back();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'        => 'success',
                'masked_email'  => OtpVerification::maskEmail($purchase->email),
                'ttl_minutes'   => TenderDeviceOtp::OTP_TTL_MIN,
            ]);
        }

        Session::flash('success', 'A new verification code was sent to ' . $purchase->email . '.');
        return back();
    }

    /**
     * Admin-side counterpart to Front\FindMyFilesController::verifyDeviceOtp
     * — lets an admin type in the code the customer reads out to them
     * (phone/chat support), completing the device recognition without the
     * customer clicking any link themself.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'otp_code'  => 'required|string|size:6|regex:/^\d{6}$/',
        ]);

        $device = TenderDeviceRegistration::findOrFail($request->device_id);

        $otp = TenderDeviceOtp::where('order_id', $device->order_id)
            ->where('device_hash', $device->device_hash)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$otp || !$otp->isUsable()) {
            return response()->json(['status' => 'error', 'type' => 'expired']);
        }

        if (!$otp->verifyOtp($request->input('otp_code'))) {
            $otp->increment('attempts');
            if ($otp->attempts >= TenderDeviceOtp::MAX_ATTEMPTS) {
                $otp->update(['status' => 'exhausted']);
                return response()->json(['status' => 'error', 'type' => 'exhausted']);
            }
            return response()->json(['status' => 'error', 'type' => 'invalid_code']);
        }

        $otp->update(['status' => 'verified']);
        $device->update(['status' => 'active', 'last_used_at' => now()]);
        $device->increment('otp_uses');

        \App\TenderAuditLog::record('device_verified_by_admin', TenderPurchase::where('order_number', $device->order_id)->first(),
            'Device OTP verified by admin', ['order' => $device->order_id, 'device_id' => $device->id]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Lets an admin register a device directly (support cases — e.g. the
     * buyer can't complete the email OTP themself). Skips the OTP step
     * entirely; the device is active immediately.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id'     => 'required|string|max:50',
            'device_label' => 'required|string|max:255',
        ]);

        $purchase = TenderPurchase::where('order_number', $request->order_id)->first();
        if (!$purchase) {
            Session::flash('error', 'No order found with that order number.');
            return back();
        }

        $count = TenderDeviceRegistration::where('order_id', $request->order_id)
            ->whereIn('status', ['pending', 'active'])
            ->count();
        $max = (int) optional(\App\BasicExtra::first())->tender_max_devices_per_order ?: 5;
        if ($count >= $max) {
            Session::flash('error', 'This order is already at its device limit.');
            return back();
        }

        // Admin-added devices have no real User-Agent to hash against, so a
        // synthetic one keyed to this exact submission — it just needs to be
        // unique and stable, it never has to match a real request.
        $deviceHash = hash('sha256', 'admin-added:' . $request->order_id . ':' . \Illuminate\Support\Str::random(16));

        TenderDeviceRegistration::create([
            'order_id'          => $request->order_id,
            'device_hash'       => $deviceHash,
            'device_label'      => $request->device_label,
            'device_type'       => 'desktop',
            'status'            => 'active',
            'is_primary'        => $count === 0,
            'validation_method' => 'admin_manual',
            'registered_at'     => now(),
            'last_used_at'      => now(),
        ]);

        \App\TenderAuditLog::record('device_added_manually', $purchase,
            'Device added manually by admin', ['order' => $request->order_id, 'label' => $request->device_label]);

        Session::flash('success', 'Device added and authorized.');
        return back();
    }
}
