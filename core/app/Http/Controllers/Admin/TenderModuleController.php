<?php

namespace App\Http\Controllers\Admin;

use App\Tender;
use App\TenderModule;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TenderModuleController extends Controller
{
    public function index($id)
    {
        $tender  = Tender::findOrFail($id);
        $modules = TenderModule::where('tender_id', $tender->id)->get();

        return view('admin.tender.module.index', compact('tender', 'modules'));
    }

    /** Document types a tender module may carry. Executables/scripts/SVG excluded. */
    private const ALLOWED_FILE_EXT = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip'];

    /**
     * Validate an LFM file reference before it is copied into the public assets
     * directory, and return the safe destination filename.
     *
     * Blocks two attacks on the user-supplied `tender_file` field:
     *  - a disallowed extension (e.g. .php/.phtml) that would be web-executable
     *    once copied under assets/;
     *  - a stream wrapper or off-site URL (file://, php://, http://other-host)
     *    that would turn copy() into an SSRF / local-file-read primitive.
     *
     * @throws \RuntimeException if the reference is unsafe
     */
    private function safeUploadFilename(string $fileRef): string
    {
        $parts = parse_url($fileRef);
        if ($parts === false) {
            throw new \RuntimeException('Invalid file reference.');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if ($scheme !== '' && $scheme !== 'http' && $scheme !== 'https') {
            throw new \RuntimeException('Unsupported file source.');
        }
        if (!empty($parts['host']) && strcasecmp($parts['host'], request()->getHost()) !== 0) {
            throw new \RuntimeException('Files must be selected from this site.');
        }

        $filename = basename(rawurldecode($parts['path'] ?? $fileRef));
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, self::ALLOWED_FILE_EXT, true)) {
            throw new \RuntimeException('Only these file types are allowed: ' . implode(', ', self::ALLOWED_FILE_EXT) . '.');
        }

        return $filename;
    }

    /**
     * Secure, NON-public storage for tender module files (outside the web root),
     * so the paid documents can only be delivered through the token-guarded,
     * watermarked download endpoint — never by a direct URL.
     */
    private function modulesDir(): string
    {
        $dir = storage_path('app/tender_modules');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    /**
     * Copy the LFM-picked file into secure storage, then delete the public LFM
     * source so no web-reachable copy of the tender document remains.
     */
    private function importTenderFile(string $lfmRef, string $filename): void
    {
        @copy($lfmRef, $this->modulesDir() . '/' . $filename);
        $this->deletePublicSource($lfmRef);
    }

    /** Remove a file under the public /assets tree, with path-containment guard. */
    private function deletePublicSource(string $ref): void
    {
        $path = parse_url($ref, PHP_URL_PATH) ?: $ref;
        $pos  = strpos($path, '/assets/');
        if ($pos === false) {
            return;
        }
        $rel   = ltrim(rawurldecode(substr($path, $pos)), '/'); // assets/lfm/files/x.zip
        $local = base_path('../' . $rel);
        $real  = realpath($local);
        $root  = realpath(base_path('../assets'));
        if ($real && $root && strncmp($real, $root, strlen($root)) === 0) {
            @unlink($real);
        }
    }

    /**
     * One-off browser-run migration (no CLI needed): copy every module file into
     * secure storage, and with ?cleanup=1 also delete the public copies. Gated by
     * the Tender Management permission (this route lives in that group).
     */
    public function migrateFiles(Request $request)
    {
        @set_time_limit(0);
        $cleanup = $request->query('cleanup') == '1';

        $dest = $this->modulesDir();
        $publicTenderDir = realpath(base_path(FRONT_TENDER_FILES_DIR));
        $lfmRoot         = realpath(base_path(FRONT_LFM_FILES_DIR));

        $findInLfm = function ($name) use ($lfmRoot) {
            if (!$lfmRoot || !is_dir($lfmRoot)) return null;
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($lfmRoot, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                if ($f->isFile() && $f->getFilename() === $name) return $f->getPathname();
            }
            return null;
        };

        $copied = 0; $already = 0; $missing = []; $sources = [];

        foreach (TenderModule::whereNotNull('tender_file')->get() as $m) {
            $name = trim((string) $m->tender_file);
            if ($name === '') continue;

            $target = $dest . '/' . $name;
            if (file_exists($target)) {
                $already++;
            } else {
                $src = ($publicTenderDir && file_exists($publicTenderDir . '/' . $name))
                    ? $publicTenderDir . '/' . $name
                    : $findInLfm($name);
                if ($src && @copy($src, $target)) {
                    $copied++;
                } else {
                    $missing[] = $name;
                    continue;
                }
            }

            if ($publicTenderDir && file_exists($publicTenderDir . '/' . $name)) {
                $sources[] = $publicTenderDir . '/' . $name;
            }
            if ($lfm = $findInLfm($name)) {
                $sources[] = $lfm;
            }
        }

        $deleted = 0;
        if ($cleanup) {
            foreach (array_unique($sources) as $s) {
                if (@unlink($s)) $deleted++;
            }
        }

        // Also drop the .htaccess "deny all" guards on the public dirs.
        $guards = $this->writeHtaccessGuards();

        $lines = [
            'Tender file migration',
            '======================',
            'copied to storage : ' . $copied,
            'already in storage: ' . $already,
            'missing (re-upload these modules): ' . (count($missing) ? implode(', ', array_unique($missing)) : 'none'),
            $cleanup
                ? 'deleted public copies: ' . $deleted
                : 'public copies that WOULD be deleted with ?cleanup=1: ' . count(array_unique($sources)),
            '',
            '.htaccess guards:',
        ];
        foreach ($guards as $g) {
            $lines[] = '  ' . $g;
        }
        $lines[] = '';
        $lines[] = $cleanup
            ? 'Done. Now REMOVE the admin.tender.migrate_files route.'
            : 'Dry run. After testing a real download, visit this page with ?cleanup=1 to delete the public copies.';

        return response('<pre>' . e(implode("\n", $lines)) . '</pre>');
    }

    /**
     * Write a "deny all direct HTTP access" .htaccess into each public dir that
     * may hold tender documents / receipts. Returns a per-dir status list.
     */
    private function writeHtaccessGuards(): array
    {
        $content = "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n"
                 . "<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n";

        $dirs = [
            base_path(FRONT_TENDER_FILES_DIR),
            base_path(rtrim(FRONT_TENDER_INVOICE_DIR, '/')),
            base_path(FRONT_LFM_FILES_DIR),
        ];

        $result = [];
        foreach ($dirs as $dir) {
            $label = 'assets/' . substr($dir, strpos($dir, 'assets/') + 7);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (!is_dir($dir)) {
                $result[] = $label . ' → SKIPPED (no dir)';
                continue;
            }
            $ok = @file_put_contents($dir . '/.htaccess', $content) !== false;
            $result[] = $label . ' → ' . ($ok ? 'protected' : 'FAILED (permissions — create manually)');
        }
        return $result;
    }

    public function store(Request $request)
    {
        $rules = [
            'name'    => 'required',
            'summary' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $module           = new TenderModule;
        $module->tender_id = $request->tender_id;
        $module->name     = $request->name;
        $module->cost     = $request->cost ?: null;
        $module->summary  = $request->summary;

        if ($request->filled('tender_file')) {
            try {
                $filename = $this->safeUploadFilename($request->tender_file);
            } catch (\RuntimeException $e) {
                return response()->json(['error' => 'true', 'tender_file' => [$e->getMessage()]]);
            }
            $this->importTenderFile($request->tender_file, $filename);
            $module->tender_file = $filename;
        }

        $module->save();

        Tender::findOrFail($request->tender_id)->recalculatePrice();

        Session::flash('success', 'Tender Module Added Successfully');

        return 'success';
    }

    public function update(Request $request)
    {
        $rules = [
            'name'    => 'required',
            'summary' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $module          = TenderModule::findOrFail($request->module_id);
        $module->name    = $request->name;
        $module->cost    = $request->cost ?: null;
        $module->summary = $request->summary;

        if ($request->filled('tender_file')) {
            try {
                $filename = $this->safeUploadFilename($request->tender_file);
            } catch (\RuntimeException $e) {
                return response()->json(['error' => 'true', 'tender_file' => [$e->getMessage()]]);
            }

            if (!empty($module->tender_file)) {
                @unlink($this->modulesDir() . '/' . $module->tender_file);
            }

            $this->importTenderFile($request->tender_file, $filename);
            $module->tender_file = $filename;
        }

        $module->save();

        Tender::findOrFail($module->tender_id)->recalculatePrice();

        Session::flash('success', 'Tender Module Updated Successfully');

        return 'success';
    }

    public function delete(Request $request)
    {
        $module = TenderModule::findOrFail($request->module_id);

        if ($module->sections->count() > 0) {
            Session::flash('warning', 'First Delete All The Sections of This Module');
            return back();
        }

        if (!empty($module->tender_file)) {
            @unlink($this->modulesDir() . '/' . $module->tender_file);
        }

        $tenderId = $module->tender_id;
        $module->delete();

        Tender::findOrFail($tenderId)->recalculatePrice();

        Session::flash('success', 'Tender Module Deleted Successfully');

        return back();
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            $module = TenderModule::findOrFail($id);

            if ($module->sections->count() > 0) {
                Session::flash('warning', 'First Delete All The Sections of Those Modules');
                return 'success';
            }
        }

        foreach ($request->ids as $id) {
            $module = TenderModule::findOrFail($id);

            if (!empty($module->tender_file)) {
                @unlink(FRONT_TENDER_FILES_PATH . $module->tender_file);
            }

            $module->delete();
        }

        Session::flash('success', 'Tender Modules Deleted Successfully');

        return 'success';
    }

    /** Stream a module's stored file for admin verification (inline for PDFs, download otherwise). */
    public function viewFile($id)
    {
        $module = TenderModule::findOrFail($id);

        if (empty($module->tender_file)) {
            abort(404);
        }

        $path = $this->modulesDir() . '/' . $module->tender_file;
        if (!is_file($path)) {
            abort(404);
        }

        $ext        = strtolower(pathinfo($module->tender_file, PATHINFO_EXTENSION));
        $disposition = $ext === 'pdf' ? 'inline' : 'attachment';

        return response()->file($path, [
            'Content-Disposition' => $disposition . '; filename="' . $module->tender_file . '"',
        ]);
    }

    public function status(Request $request)
    {
        $module         = TenderModule::findOrFail($request->id);
        $module->status = $request->status;
        $module->save();

        Tender::findOrFail($module->tender_id)->recalculatePrice();

        return response()->json(['success' => true]);
    }
}
