<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per real access by a recognized device — the "Recent Access
 * History" trail on the admin device-details page. Separate from
 * TenderDeviceRegistration.last_used_at (a single rolling value) so full
 * history survives regardless of how many times a device is used.
 */
class TenderDeviceAccessLog extends Model
{
    protected $fillable = [
        'device_registration_id',
        'ip',
        'network_label',
        'result',
        'accessed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];
}
