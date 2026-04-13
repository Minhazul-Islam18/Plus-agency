<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccessLog extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // only created_at, set manually

    protected $fillable = [
        'id', 'event_type', 'order_id', 'ip',
        'device_hash', 'user_agent', 'email_hash',
        'risk_score', 'result', 'created_at',
    ];

    protected $dates = ['created_at'];

    // Event type constants
    const LINK_REQUESTED       = 'LINK_REQUESTED';
    const LINK_SENT            = 'LINK_SENT';
    const LINK_CLICKED         = 'LINK_CLICKED';
    const DOWNLOAD_SUCCESS     = 'DOWNLOAD_SUCCESS';
    const DOWNLOAD_FAILED      = 'DOWNLOAD_FAILED';
    const RATE_LIMIT_TRIGGERED = 'RATE_LIMIT_TRIGGERED';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public static function record(string $eventType, array $data = [])
    {
        static::create(array_merge(['event_type' => $eventType], $data));
    }
}
