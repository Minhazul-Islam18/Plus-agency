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
            $filePath = $request->tender_file;
            $dir      = 'assets/front/files/tender_modules/';
            @mkdir($dir, 0775, true);
            @copy($filePath, $dir . $filename);
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
                @unlink('assets/front/files/tender_modules/' . $module->tender_file);
            }

            $filePath = $request->tender_file;
            $dir      = 'assets/front/files/tender_modules/';
            @mkdir($dir, 0775, true);
            @copy($filePath, $dir . $filename);
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
            @unlink('assets/front/files/tender_modules/' . $module->tender_file);
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
                @unlink('assets/front/files/tender_modules/' . $module->tender_file);
            }

            $module->delete();
        }

        Session::flash('success', 'Tender Modules Deleted Successfully');

        return 'success';
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
