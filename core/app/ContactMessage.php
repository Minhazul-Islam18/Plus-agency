<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
  protected $table = 'contact_messages';

  protected $fillable = [
    'name',
    'email',
    'subject',
    'message',
    'status',
    'mail_sent',
    'reply_message',
    'replied_at',
    'replied_by'
  ];

  protected $casts = [
    'replied_at' => 'datetime',
  ];

  public function isPending(): bool
  {
    return $this->status === 'pending';
  }

  public function isApproved(): bool
  {
    return $this->status === 'approved';
  }

  public function isRejected(): bool
  {
    return $this->status === 'rejected';
  }

  public function isReplied(): bool
  {
    return !empty($this->replied_at);
  }
}
