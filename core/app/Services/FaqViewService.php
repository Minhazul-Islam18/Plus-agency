<?php

namespace App\Services;

use App\BasicSetting;
use App\Faq;
use Illuminate\Support\Facades\DB;

/**
 * "Most viewed questions" panel on the FAQ page.
 *
 * A question is promoted onto the panel once it has MORE than THRESHOLD
 * counted views. The panel holds at most `basic_settings.faq_frequent_max`
 * questions (per language); when a new one is promoted into a full panel,
 * the one that has been on it the longest (oldest promoted_at) is dropped —
 * first in, first out. Nothing here touches views_count except the +1 on a
 * counted view: a dropped question keeps its count, so its next view
 * promotes it again (and drops the then-oldest one).
 *
 * All writes go through the query builder, NOT $faq->save(): the Faq model
 * purges the site's HTTP caches on every save, which would happen on every
 * click. The panel itself is loaded live by the page (JSON, no-store), so a
 * promotion needs no cache purge either.
 */
class FaqViewService
{
    /** Views needed (exclusive) before a question is promoted. */
    public const THRESHOLD = 5;

    public const DEFAULT_MAX = 5;

    public static function max(int $langId): int
    {
        $max = (int) BasicSetting::where('language_id', $langId)->value('faq_frequent_max');

        return $max > 0 ? $max : self::DEFAULT_MAX;
    }

    /** Count one view of an active question and promote it if it qualifies. */
    public static function record(int $faqId): void
    {
        DB::transaction(function () use ($faqId) {
            $faq = Faq::where('id', $faqId)->where('status', 1)->lockForUpdate()->first();
            if (!$faq) {
                return;
            }

            DB::table('faqs')->where('id', $faq->id)->increment('views_count');

            if ($faq->is_frequent || $faq->views_count + 1 <= self::THRESHOLD) {
                return;
            }

            DB::table('faqs')->where('id', $faq->id)->update([
                'is_frequent' => 1,
                'promoted_at' => now(),
            ]);

            self::trimToMax((int) $faq->language_id);
        });
    }

    /** Drop the oldest panel entries until it fits the configured maximum. */
    public static function trimToMax(int $langId): void
    {
        $max = self::max($langId);

        $excess = DB::table('faqs')
            ->where('language_id', $langId)
            ->where('status', 1)
            ->where('is_frequent', 1)
            ->orderBy('promoted_at', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->slice(0, -$max ?: null);

        // slice(0, -$max) keeps the newest $max ids; everything before is excess.
        if ($excess->isNotEmpty()) {
            DB::table('faqs')->whereIn('id', $excess->all())->update(['is_frequent' => 0]);
        }
    }

    /** Panel entries, oldest promotion first (queue order). */
    public static function panel(int $langId)
    {
        return Faq::where('language_id', $langId)
            ->where('status', 1)
            ->where('is_frequent', 1)
            ->orderBy('promoted_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(self::max($langId))
            ->get()
            ->reverse()
            ->values();
    }
}
