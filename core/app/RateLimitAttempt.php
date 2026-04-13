<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RateLimitAttempt extends Model
{
    protected $fillable = ['key', 'attempts', 'blocked_until', 'last_attempt_at'];

    protected $dates = ['blocked_until', 'last_attempt_at'];

    protected $casts = [
        'blocked_until'   => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    public function isBlocked(): bool
    {
        return $this->blocked_until && $this->blocked_until->isFuture();
    }

    public function minutesUntilUnblock(): int
    {
        if (!$this->isBlocked()) {
            return 0;
        }
        return (int) ceil(now()->diffInMinutes($this->blocked_until, false) * -1);
    }
}
