<?php

namespace App\Jobs;

use App\Http\Helpers\KreativMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queues a KreativMailer::mailFromAdmin() send — same $data shape the
 * synchronous helper takes. Callers that were already fire-and-forget
 * (return value unused, wrapped in try/catch) can dispatch this instead of
 * calling mailFromAdmin() directly, so the SMTP round-trip doesn't hold a
 * PHP-FPM worker for the duration of the request.
 */
class SendAdminMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected array $data)
    {
    }

    public function handle(): void
    {
        try {
            (new KreativMailer)->mailFromAdmin($this->data);
        } catch (\Exception $e) {
            Log::error('[SendAdminMail] Send failed', [
                'templateType' => $this->data['templateType'] ?? null,
                'toMail'       => $this->data['toMail'] ?? null,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}
