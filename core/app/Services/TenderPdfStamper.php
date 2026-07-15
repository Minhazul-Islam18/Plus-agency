<?php

namespace App\Services;

use RuntimeException;
use Throwable;

/**
 * Applies a personalised, semi-transparent diagonal watermark to a PDF using
 * the licensed SetaPDF-Stamper library (ionCube encoded).
 *
 * Every generated PDF is traceable to the buyer who downloaded it:
 *   {company} / Downloaded by: {name} / Tender ID: {tender_code} / {datetime}
 *
 * Failure is fatal by design — the caller must not serve an un-stamped file.
 */
class TenderPdfStamper
{
    /** @var bool */
    private static $loaded = false;

    /** Manual (non-Composer) library autoload — e.g. the ionCube trial in core/setapdf/. */
    private function libPath(): string
    {
        return base_path('setapdf/library/SetaPDF/Autoload.php');
    }

    /**
     * Watermark font, first match wins.
     *
     * Noto Sans Bold is the intended face: a modern humanist sans that stays legible
     * at watermark opacity, with full Latin-Extended coverage (French accents, €).
     * DejaVu is kept last as a fallback so an installation that only ships the
     * SetaPDF demo font still stamps rather than failing closed.
     */
    private function fontPath(): ?string
    {
        $candidates = [
            base_path('setapdf/fonts/NotoSans-Bold.ttf'),
            resource_path('fonts/NotoSans-Bold.ttf'),
            base_path('setapdf/fonts/NotoSans-Regular.ttf'),
            resource_path('fonts/NotoSans-Regular.ttf'),
            base_path('setapdf/fonts/DejaVuSans.ttf'),
            resource_path('fonts/DejaVuSans.ttf'),
        ];
        foreach ($candidates as $p) {
            if (is_file($p)) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Make the SetaPDF classes available once.
     *
     * Works with EITHER:
     *   (a) the purchased source package installed via Composer
     *       (setasign/setapdf-stamper) — classes autoload, no ionCube needed; or
     *   (b) a manually-placed library in core/setapdf/ (e.g. the ionCube trial).
     *
     * @throws RuntimeException if neither is usable.
     */
    private function boot(): void
    {
        if (self::$loaded) {
            return;
        }

        // (a) Composer package already registers the SetaPDF autoloader.
        if (!class_exists('SetaPDF_Stamper')) {
            // (b) Fall back to a hand-placed library.
            $autoload = $this->libPath();
            if (is_file($autoload)) {
                require_once $autoload;
            }
        }

        if (!class_exists('SetaPDF_Stamper')) {
            // The encoded trial additionally needs the ionCube loader — give a precise hint.
            if (is_file($this->libPath()) && !extension_loaded('ionCube Loader')) {
                throw new RuntimeException('SetaPDF library is present but the ionCube Loader is not enabled for this PHP SAPI.');
            }
            throw new RuntimeException('SetaPDF not available. Install setasign/setapdf-stamper via Composer, or place the library in core/setapdf/.');
        }

        self::$loaded = true;
    }

    /**
     * Process $srcPath (watermark and/or password-encrypt) and write $destPath.
     *
     * @param array $settings watermark, template, opacity, color, font_size,
     *                        rotation, encrypt, password
     * @param array $vars     placeholder => value
     *
     * @throws RuntimeException on any failure
     */
    public function stampFile(string $srcPath, string $destPath, array $settings, array $vars): void
    {
        $this->boot();

        if (!is_file($srcPath)) {
            throw new RuntimeException('Source PDF missing: ' . $srcPath);
        }

        // Watermark defaults on for backward compatibility; encryption off.
        $doWatermark = array_key_exists('watermark', $settings) ? (bool) $settings['watermark'] : true;
        $doEncrypt   = !empty($settings['encrypt']);
        $password    = (string) ($settings['password'] ?? '');

        if (!$doWatermark && !$doEncrypt) {
            throw new RuntimeException('Nothing to do: watermark and encryption both disabled.');
        }
        if ($doEncrypt && $password === '') {
            throw new RuntimeException('Encryption enabled but password is empty.');
        }

        $text     = '';
        $fontFile = null;
        if ($doWatermark) {
            $fontFile = $this->fontPath();
            if ($fontFile === null) {
                throw new RuntimeException('Watermark font (DejaVuSans.ttf) not found in any known location.');
            }
            $text = $this->buildText((string) ($settings['template'] ?? ''), $vars);
            if ($text === '') {
                throw new RuntimeException('Watermark text resolved to empty.');
            }
        }

        [$r, $g, $b] = $this->hexToRgb((string) ($settings['color'] ?? 'FF0000'));
        $opacity  = $this->clampFloat($settings['opacity'] ?? 0.30, 0.05, 1.0);
        $fontSize = (int) ($settings['font_size'] ?? 24);
        $fontSize = $fontSize > 0 ? min($fontSize, 96) : 24;
        $rotation = (int) ($settings['rotation'] ?? 45);

        try {
            $writer   = new \SetaPDF_Core_Writer_File($destPath);
            $document = \SetaPDF_Core_Document::loadByFilename($srcPath, $writer);

            if ($doWatermark) {
                $stamper = new \SetaPDF_Stamper($document);

                $font  = new \SetaPDF_Core_Font_TrueType_Subset($document, $fontFile);
                $stamp = new \SetaPDF_Stamper_Stamp_Text($font, $fontSize);
                $stamp->setText($text);
                $stamp->setPadding(8);
                $stamp->setLineHeight($fontSize + 4);
                $stamp->setTextColor(new \SetaPDF_Core_DataStructure_Color_Rgb($r, $g, $b));
                $stamp->setOpacity($opacity);

                $stamper->addStamp($stamp, [
                    'position'   => \SetaPDF_Stamper::POSITION_CENTER_MIDDLE,
                    'showOnPage' => \SetaPDF_Stamper::PAGES_ALL,
                    'rotation'   => $rotation,
                ]);

                $stamper->stamp();
            }

            if ($doEncrypt) {
                // AES-256 (PDF 2.0) with an OWNER password only. The user
                // (open) password is empty, so buyers open the file with no
                // prompt — but editing is locked behind the owner password,
                // which only admins hold. Print/copy/accessibility stay
                // allowed; modify/annotate/fill/assemble are denied.
                $secHandler = \SetaPDF_Core_SecHandler_Standard_Aes256::factory(
                    $document,
                    $password,   // owner password (admin-only)
                    '',          // user password (empty → opens freely)
                    \SetaPDF_Core_SecHandler::PERM_PRINT
                        | \SetaPDF_Core_SecHandler::PERM_DIGITAL_PRINT
                        | \SetaPDF_Core_SecHandler::PERM_COPY
                        | \SetaPDF_Core_SecHandler::PERM_ACCESSIBILITY
                );
                $document->setSecHandler($secHandler);
            }

            $document->save()->finish();
        } catch (Throwable $e) {
            @unlink($destPath);
            throw new RuntimeException('PDF processing failed: ' . $e->getMessage(), 0, $e);
        }

        if (!is_file($destPath) || filesize($destPath) === 0) {
            @unlink($destPath);
            throw new RuntimeException('Processed PDF was not produced.');
        }
    }

    /**
     * Resolve {placeholder} tokens against provided values. Unknown tokens are
     * stripped; blank lines are dropped.
     */
    private function buildText(string $template, array $vars): string
    {
        if (trim($template) === '') {
            $template = "{company}\nDownloaded by: {name}\nTender ID: {tender_code}\n{datetime}";
        }

        $replaced = preg_replace_callback('/\{(\w+)\}/', function ($m) use ($vars) {
            return array_key_exists($m[1], $vars) ? (string) $vars[$m[1]] : '';
        }, $template);

        $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $replaced)), function ($l) {
            return $l !== '';
        });

        return implode("\n", $lines);
    }

    /**
     * '#FF0000' | 'FF0000' | 'f00' → [r, g, b] as floats 0..1.
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = 'FF0000';
        }

        return [
            (float) (hexdec(substr($hex, 0, 2)) / 255),
            (float) (hexdec(substr($hex, 2, 2)) / 255),
            (float) (hexdec(substr($hex, 4, 2)) / 255),
        ];
    }

    private function clampFloat($value, float $min, float $max): float
    {
        $value = (float) $value;
        return max($min, min($max, $value));
    }
}
