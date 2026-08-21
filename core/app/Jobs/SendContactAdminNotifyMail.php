<?php

namespace App\Jobs;

use App\ContactMessage;
use App\Http\Helpers\KreativMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Same as SendAdminMail, but for the one call site that used the
 * synchronous return value for something: FrontendController@sendmail sets
 * ContactMessage.mail_sent = 1 once the admin-notify email actually goes
 * out. Queuing the send means that flag can only be set once the job runs,
 * not at request time — so the job does it itself, after a real send.
 */
class SendContactAdminNotifyMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected array $data, protected int $contactMessageId)
    {
    }

    public function handle(): void
    {
        try {
            $sent = (new KreativMailer)->mailFromAdmin($this->data);
            if ($sent) {
                ContactMessage::where('id', $this->contactMessageId)->update(['mail_sent' => 1]);
            }
        } catch (\Exception $e) {
            Log::error('[SendContactAdminNotifyMail] Send failed', [
                'contact_message_id' => $this->contactMessageId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
