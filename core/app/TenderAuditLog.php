<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Append-only audit record of an admin action on a tender purchase.
 *
 * Records are never updated; only created_at is tracked.
 */
class TenderAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'admin_id', 'admin_name', 'action', 'tender_purchase_id',
        'order_number', 'description', 'meta', 'ip', 'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Write an audit entry. Best-effort: logging must never break the action it
     * records, so failures are swallowed (and surfaced to the app log).
     */
    public static function record(string $action, ?TenderPurchase $purchase = null, string $description = '', array $meta = []): void
    {
        try {
            $admin = Auth::guard('admin')->user();

            static::create([
                'admin_id'           => $admin->id ?? null,
                'admin_name'         => $admin->name ?? null,
                'action'             => $action,
                'tender_purchase_id' => $purchase?->id,
                'order_number'       => $purchase?->order_number,
                'description'        => $description !== '' ? Str::limit($description, 500, '') : null,
                'meta'               => $meta ?: null,
                'ip'                 => request()->ip(),
                'user_agent'         => substr((string) request()->userAgent(), 0, 255),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Tender audit log failed: ' . $e->getMessage());
        }
    }
}
