<?php

namespace App\Console\Commands;

use App\BasicExtra;
use App\Http\Controllers\Payment\Tender\TenderPaymentHelper;
use App\TenderPurchase;
use Illuminate\Console\Command;

/**
 * Catches EVERY way an online payment can go unfinished, not just the ones
 * the gateway explicitly reports back to us (a declined card, an abandoned
 * hosted-checkout page). A buyer who closes the tab mid-payment, gives up on
 * an OTP prompt, loses network, or whose browser crashes never sends any
 * request back to our server at all — TenderPaymentHelper::handleFailedPayment()
 * only ever fires from a gateway's own cancel/notify redirect, so none of
 * those cases were ever caught.
 *
 * This sweeps for Pending online orders old enough that they're clearly not
 * still mid-payment, and haven't already been notified once (real-time
 * failure or a previous sweep both set resume_token_hash), and sends the
 * same incomplete-payment resume email for them.
 */
class NotifyIncompleteTenderPayments extends Command
{
    use TenderPaymentHelper;

    protected $signature = 'tender:notify-incomplete-payments {--minutes= : Age threshold override — only orders older than this are considered abandoned. Defaults to the admin-configurable Settings → Payment Session Timeout when omitted.}';

    protected $description = 'Email buyers whose online tender payment never completed and was never reported as failed/cancelled by the gateway.';

    public function handle(): int
    {
        $minutes = $this->option('minutes');
        if ($minutes === null) {
            $minutes = (int) (optional(BasicExtra::first())->tender_payment_session_timeout_minutes ?: 5);
        }

        $cutoff = now()->subMinutes((int) $minutes);

        $stale = TenderPurchase::with('tender.language')
            ->where('payment_status', 'Pending')
            ->where('gateway_type', 'online')
            ->whereNull('resume_token_hash')
            ->where('created_at', '<=', $cutoff)
            ->get();

        $sent = 0;
        foreach ($stale as $purchase) {
            // handleFailedPayment() resolves the sender's language via
            // getLang() -> currentLang() -> app()->getLocale() — there is no
            // request/URL locale here (this runs from cron), so it has to be
            // set explicitly per purchase, same as TenderController::resumePurchase().
            if ($purchase->tender && $purchase->tender->language) {
                app()->setLocale($purchase->tender->language->code);
            }

            $this->handleFailedPayment($purchase);
            $sent++;
        }

        $this->info("Notified {$sent} abandoned online payment(s).");

        return self::SUCCESS;
    }
}
