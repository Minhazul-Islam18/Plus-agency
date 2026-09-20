<?php

namespace App\Console\Commands;

use App\BasicExtended;
use App\BasicExtra;
use App\BasicSetting;
use App\Blog;
use App\EmailTemplate;
use App\Member;
use App\OfflineGateway;
use App\Page;
use App\Portfolio;
use App\Service;
use App\Tender;
use App\TenderModule;
use Illuminate\Console\Command;

/**
 * Every Summernote editor in admin (Service/Blog/Portfolio content, the
 * cookie/footer/invoice text blocks, email templates, page builder body,
 * tender overview/expert details, member details, offline gateway
 * instructions...) uploads images into one shared folder:
 * assets/front/img/summernote/. Deleting an image from a Summernote editor
 * only removes the <img> tag from that one field's HTML — nothing tells the
 * server the file is now unused (it might still be referenced from another
 * field entirely), so files are never deleted at upload-remove time (see
 * the conversation this command came out of). This instead runs once a day
 * and sweeps the folder for files no longer referenced ANYWHERE across all
 * of those fields.
 *
 * Matches by bare filename substring rather than reconstructing each
 * field's exact stored URL form — content saved before the {base_url}
 * encoding fix (see SummernoteController/replaceBaseUrl) may still contain
 * either "{base_url}/..." or the corrupted "%7Bbase_url%7D/..." form, and
 * the uniqid() filename itself is what's actually unique, so it's the only
 * thing worth matching on.
 */
class CleanupOrphanedSummernoteImages extends Command
{
    protected $signature = 'summernote:cleanup-orphaned-images
        {--dry-run : List what would be deleted without deleting anything}
        {--grace-hours=24 : Skip files newer than this many hours, so an image just uploaded into a not-yet-saved form is never deleted out from under it}';

    protected $description = 'Delete summernote-uploaded images no longer referenced by any admin content field.';

    private const SUBDIR = 'summernote/';

    public function handle(): int
    {
        // FRONT_IMG_PATH ('assets/front/img/') is relative, and this repo
        // serves its assets/ folder from ONE LEVEL ABOVE the Laravel app
        // root (core/), not from core/public/assets — a plain web request
        // happens to get the right CWD for that relative path, but a CLI/
        // cron invocation of artisan does not (confirmed: running this
        // command from core/ with the bare relative path resolved to a
        // nonexistent core/assets/front/img/summernote/). base_path() is
        // always core/ regardless of invocation CWD, so anchor off that.
        $dir = dirname(base_path()) . '/' . FRONT_IMG_PATH . self::SUBDIR;

        if (!is_dir($dir)) {
            $this->info('No summernote upload directory found — nothing to do.');
            return self::SUCCESS;
        }

        $graceHours = (int) $this->option('grace-hours');
        $cutoff = now()->subHours($graceHours)->getTimestamp();

        $haystack = $this->allReferencingContent();

        $deleted = 0;
        $kept = 0;
        $skippedYoung = 0;

        foreach (scandir($dir) as $filename) {
            if ($filename === '.' || $filename === '..' || !is_file($dir . $filename)) {
                continue;
            }

            if (filemtime($dir . $filename) > $cutoff) {
                $skippedYoung++;
                continue;
            }

            if (str_contains($haystack, $filename)) {
                $kept++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("Would delete: {$filename}");
            } else {
                @unlink($dir . $filename);
            }
            $deleted++;
        }

        $verb = $this->option('dry-run') ? 'Would delete' : 'Deleted';
        $this->info("{$verb} {$deleted} orphaned file(s). Kept {$kept} still-referenced, skipped {$skippedYoung} too-recent-to-judge.");

        return self::SUCCESS;
    }

    private function allReferencingContent(): string
    {
        $parts = [
            Service::pluck('content')->implode(' '),
            Blog::pluck('content')->implode(' '),
            Portfolio::pluck('content')->implode(' '),
            EmailTemplate::pluck('email_body')->implode(' '),
            Page::pluck('body')->implode(' '),
            TenderModule::pluck('summary')->implode(' '),
            Tender::pluck('overview')->implode(' '),
            Tender::pluck('expert_details')->implode(' '),
            Member::pluck('details')->implode(' '),
            OfflineGateway::pluck('instructions')->implode(' '),
            BasicSetting::pluck('copyright_text')->implode(' '),
            BasicExtended::pluck('cookie_alert_text')->implode(' '),
            BasicExtra::pluck('invoice_footer_address')->implode(' '),
        ];

        return implode(' ', $parts);
    }
}
