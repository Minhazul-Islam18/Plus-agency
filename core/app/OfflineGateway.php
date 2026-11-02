<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfflineGateway extends Model
{
    protected $fillable = ['id', 'language_id', 'name', 'short_description', 'instructions', 'serial_number', 'status', 'is_receipt', 'receipt'];

    public function offline_gateway() {
        return $this->belongsTo('App\OfflineGateway');
    }

    /**
     * Instructions with form controls flattened to plain text. The editor can
     * hold pasted <select>/<input> markup; in the admin panel the recipient
     * details are display-only, so a dropdown becomes its selected option
     * (or first option) and inputs become their value.
     */
    public function readOnlyInstructions(): string
    {
        $html = trim((string) $this->instructions);
        if ($html === '') {
            return '';
        }

        $html = preg_replace_callback('~<select\b[^>]*>(.*?)</select>~is', static function ($m) {
            preg_match_all('~<option\b([^>]*)>(.*?)</option>~is', $m[1], $opts, PREG_SET_ORDER);
            $pick = null;
            foreach ($opts as $o) {
                $text = trim(strip_tags($o[2]));
                if ($text === '') {
                    continue;
                }
                if (stripos($o[1], 'selected') !== false) {
                    return e($text);
                }
                $pick = $pick ?? $text;
            }
            return e((string) $pick);
        }, $html);

        $html = preg_replace_callback('~<input\b[^>]*>~i', static function ($m) {
            return preg_match('~\b(?:value|placeholder)\s*=\s*(["\'])(.*?)\1~is', $m[0], $v) ? e($v[2]) : '';
        }, $html);

        return preg_replace('~<(textarea|button)\b[^>]*>(.*?)</\1>~is', '$2', $html);
    }

    /**
     * Instructions as label/value rows, for the admin "recipient information"
     * panel. Understands two shapes admins actually type into the editor:
     * a two-column table, or one "Label: value" per line/paragraph. Returns
     * plain-text rows (escape on output), or null when the instructions are
     * free text — callers should then show the raw HTML instead.
     */
    public function instructionRows(): ?array
    {
        $html = $this->readOnlyInstructions();
        if ($html === '') {
            return null;
        }

        $clean = static fn ($t) => trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags((string) $t), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $rows = [];

        if (stripos($html, '<table') !== false && preg_match_all('~<tr\b[^>]*>(.*?)</tr>~is', $html, $trs)) {
            foreach ($trs[1] as $tr) {
                if (preg_match_all('~<t[hd]\b[^>]*>(.*?)</t[hd]>~is', $tr, $cells) && count($cells[1]) >= 2) {
                    $label = rtrim($clean($cells[1][0]), " :：");
                    $value = $clean($cells[1][1]);
                    if ($label !== '') {
                        $rows[] = [$label, $value];
                    }
                }
            }
            if (count($rows) >= 2) {
                return $rows;
            }
            $rows = [];
        }

        $text  = preg_replace('~<br\s*/?>|</(p|div|li|tr|h[1-6])>~i', "\n", $html);
        $lines = array_values(array_filter(array_map($clean, explode("\n", $text)), fn ($l) => $l !== ''));
        foreach ($lines as $line) {
            if (preg_match('/^(.{2,80}?)\s*[:：]\s*(.+)$/u', $line, $m)
                && !preg_match('~^https?$~i', $m[1]) && strpos($m[2], '//') !== 0) {
                $rows[] = [$m[1], $m[2]];
            } elseif ($rows) {
                // A value the editor wrapped onto the next line (long addresses):
                // continue the previous row instead of losing the tail.
                $rows[count($rows) - 1][1] .= ' ' . $line;
            }
        }

        // Mostly "Label: value" lines → structured; otherwise it's prose.
        return count($rows) >= 2 && count($rows) >= 0.6 * count($lines) ? $rows : null;
    }
}
