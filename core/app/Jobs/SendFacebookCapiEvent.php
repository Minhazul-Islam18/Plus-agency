<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Queued Meta Conversions API send — mirrors SendAdminMail's shape (plain
 * array payload, ShouldQueue, tries/backoff, Log on failure). Never called
 * synchronously: the Graph API round-trip must not hold the request that
 * triggered the real-world event (page view, contact form, purchase).
 *
 * $eventData carries exactly what the caller already knows server-side:
 *   event_name, event_id (shared with the matching client-side fbq() call
 *   for Meta's own dedup), event_time, event_source_url, action_source,
 *   ip (raw, hashed below is wrong — Meta wants this one in plaintext),
 *   user_agent, fbp, fbc (raw, not hashed), em/ph (raw email/phone —
 *   hashed here, never by the caller), value, currency.
 *
 * No Facebook Business SDK — a plain Http::post() to the Graph API is all
 * this endpoint needs, and avoids an extra composer dependency entirely.
 */
class SendFacebookCapiEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        protected string $pixelId,
        protected string $accessToken,
        protected array $eventData
    ) {}

    public function handle(): void
    {
        $userData = [
            'client_ip_address' => $this->eventData['ip'] ?? null,
            'client_user_agent' => $this->eventData['user_agent'] ?? null,
        ];

        if (!empty($this->eventData['fbp'])) {
            $userData['fbp'] = $this->eventData['fbp'];
        }
        if (!empty($this->eventData['fbc'])) {
            $userData['fbc'] = $this->eventData['fbc'];
        }
        // Meta's spec: PII hashed with SHA-256, lowercased/trimmed first —
        // raw email/phone never leave this process.
        if (!empty($this->eventData['em'])) {
            $userData['em'] = [hash('sha256', strtolower(trim($this->eventData['em'])))];
        }
        if (!empty($this->eventData['ph'])) {
            $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $this->eventData['ph']))];
        }

        $payload = [
            'data' => [[
                'event_name' => $this->eventData['event_name'],
                'event_time' => $this->eventData['event_time'] ?? now()->timestamp,
                'event_id' => $this->eventData['event_id'],
                'event_source_url' => $this->eventData['event_source_url'] ?? null,
                'action_source' => $this->eventData['action_source'] ?? 'website',
                'user_data' => array_filter($userData, fn($v) => $v !== null),
            ]],
        ];

        if (!empty($this->eventData['value'])) {
            $payload['data'][0]['custom_data'] = array_filter([
                'value' => $this->eventData['value'],
                'currency' => $this->eventData['currency'] ?? null,
            ]);
        }

        try {
            $response = Http::asForm()->post(
                "https://graph.facebook.com/v21.0/{$this->pixelId}/events",
                ['access_token' => $this->accessToken, 'data' => json_encode($payload['data'])]
            );

            if (!$response->successful()) {
                Log::error('[FacebookCAPI] Graph API rejected event', [
                    'event_name' => $this->eventData['event_name'],
                    'event_id' => $this->eventData['event_id'],
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[FacebookCAPI] Send failed', [
                'event_name' => $this->eventData['event_name'],
                'event_id' => $this->eventData['event_id'],
                'error' => $e->getMessage(),
            ]);
        }
    }
}
