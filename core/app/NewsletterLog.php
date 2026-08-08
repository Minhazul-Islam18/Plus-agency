<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsletterLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'subject',
        'message',
        'recipient_count',
        'failed_count',
        'sent_by',
        'sent_by_name',
    ];
}
