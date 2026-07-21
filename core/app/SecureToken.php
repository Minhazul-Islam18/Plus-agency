<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SecureToken extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'order_id', 'email_hash', 'token_hash',
        'issued_at', 'expires_at', 'max_downloads', 'download_count',
        'status', 'device_hash', 'ip', 'session_secret',
    ];

    protected $dates = ['issued_at', 'expires_at'];

    protected $casts = [
        'issued_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function isValid()
    {
        return $this->status === 'active'
            && $this->expires_at->isFuture()
            && $this->download_count < $this->max_downloads;
    }
}
