<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PushNotificationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'message',
        'button_text',
        'button_url',
        'notification_type',
        'recipient_count',
        'opened_count',
        'failed_count',
        'sent_by',
        'sent_by_name',
    ];
}
