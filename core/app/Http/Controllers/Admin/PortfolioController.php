<?php

namespace App\Http\Controllers\Admin;

use App\Service;
use App\Language;
use App\Megamenu;
use App\Portfolio;
use App\BasicSetting;
use App\BasicExtended;
use App\BasicExtra;
use App\PortfolioImage;
use App\PortfolioSector;
use App\PortfolioStatus;
use App\PortfolioDocument;
use App\PortfolioHighlight;
use App\Partner;
use App\Http\Helpers\Countries;
use App\Exports\PortfolioExport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PortfolioController extends Controller
{
    private const SLIDER_SUBDIR = 'portfolios/sliders/';
    private const FEATURED_SUBDIR = 'portfolios/featured/';
    private const LOGO_SUBDIR = 'portfolios/logos/';
    private const DOCUMENT_SUBDIR = 'portfolios/documents/';

    /** Extensions accepted by the Documents picker (LFM "file" category — see mockup: PDF, DOC, DOCX, XLSX). */
    private const ALLOWED_DOCUMENT_EXTS = ['pdf', 'doc', 'docx', 'xlsx'];

    /**
     * Parses a date the way the admin's datepicker widgets actually submit
     * it ('m/d/Y' — bootstrap-datepicker's default format; nothing in
     * create/edit.blade.php overrides it). start_date/submission_date are
     * legacy `varchar` columns, so any string saves there without complaint;
     * end_date is a real `date` column (added later, see the portfolio
     * migration), which is why a raw '09/17/2026' string reaching it
     * straight from $request->all() throws a raw MySQL "Incorrect date
     * value" error instead of a friendly validation message — this
     * normalizes it to 'Y-m-d' before it ever reaches the query.
     */
    private function parseAdminDate(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }
        try {
            return Carbon::createFromFormat('m/d/Y', trim($value))->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Shared end_date rule for store()/update(): must be a real date, and —
     * only once a Submission Date (the tender's submission deadline) is
     * actually filled in — must fall on or before it. No Submission Date
     * yet = no restriction at all, per spec.
     */
    private function endDateRule(Request $request): \Closure
    {
        return function ($attribute, $value, $fail) use ($request) {
            if (!$value) {
                return;
            }
            $end = $this->parseAdminDate($value);
            if (!$end) {
                return $fail('End Date is not a valid date.');
            }
            if ($request->filled('submission_date')) {
                $deadline = $this->parseAdminDate($request->submission_date);
                if ($deadline && $end->gt($deadline)) {
                    $fail('End Date must be on or before the Submission Date (' . $deadline->format('M d, Y') . ').');
                }
            }
        };
    }

    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();

        // Ties this page's UI language to the same selector that picks
        // which language's portfolios are shown — deterministic per the
        // ?language= param, not the ambient site_lang cookie (see
        // MenuBuilderController for the precedent this mirrors).
        app()->setLocale($lang->code);

        $lang_id = $lang->id;
        $query = Portfolio::where('language_id', $lang_id)->where('is_archived', 0);

        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('archived')) {
            $query = Portfolio::where('language_id', $lang_id)->where('is_archived', 1);
        }

        $data['portfolios'] = $query->with(['sector', 'subsector', 'statusInfo'])->orderBy('id', 'DESC')->get();
        $data['sectors'] = PortfolioSector::topLevel()->where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['statuses'] = PortfolioStatus::where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['countries'] = Countries::all();

        $data['lang_id'] = $lang_id;

        return view('admin.portfolio.index', $data);
    }

    public function create(Request $request)
    {
        if ($request->filled('language')) {
            $lang = Language::where('code', $request->language)->first();
            if ($lang) {
                app()->setLocale($lang->code);
            }
        }

        $data['services'] = Service::all();
        $data['sectors'] = PortfolioSector::topLevel()->where('status', 1)->orderBy('serial_number', 'asc')->get();
        // Empty on create — no sector picked yet, cascades via AJAX same as
        // Sector/Service/Status already do once a language is chosen.
        $data['subsectors'] = collect();
        $data['statuses'] = PortfolioStatus::where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['partners'] = Partner::where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['countries'] = Countries::all();
        $data['tportfolios'] = Portfolio::where('language_id', 0)->get();
        return view('admin.portfolio.create', $data);
    }

    public function edit($id)
    {
        $data['portfolio'] = Portfolio::with('partnerRefs')->findOrFail($id);
        if (!empty($data['portfolio']->language)) {
            app()->setLocale($data['portfolio']->language->code);
        }
        $data['services'] = Service::where('language_id', $data['portfolio']->language_id)->get();
        $data['sectors'] = PortfolioSector::topLevel()->where('language_id', $data['portfolio']->language_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
        // Pre-populated for whichever sector is already saved (if any) —
        // same reasoning as Sector/Service/Status already being fully
        // loaded upfront on edit instead of purely AJAX-cascaded.
        $data['subsectors'] = $data['portfolio']->sector_id
            ? PortfolioSector::where('parent_id', $data['portfolio']->sector_id)->where('status', 1)->orderBy('serial_number', 'asc')->get()
            : collect();
        $data['statuses'] = PortfolioStatus::where('language_id', $data['portfolio']->language_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['partners'] = Partner::where('language_id', $data['portfolio']->language_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['countries'] = Countries::all();
        return view('admin.portfolio.edit', $data);
    }

    public function sliderrmv(Request $request)
    {
        $pi = PortfolioImage::findOrFail($request->fileid);
        @unlink(FRONT_IMG_PATH . self::SLIDER_SUBDIR . $pi->image);
        $pi->delete();
        return $pi->id;
    }


    public function store(Request $request)
    {
        // Editable slug: admin-typed value wins if present, else auto-
        // generate from the title (unchanged default behavior).
        $slug = $request->filled('slug')
            ? make_slug($request->slug)
            : unique_intelligent_slug($request->title, fn ($s) => Portfolio::whereRaw('LOWER(slug) = ?', [strtolower($s)])->exists());

        $sliders = !empty($request->slider) ? explode(',', $request->slider) : [];
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);
        $logo = $request->client_logo;
        $extLogo = pathinfo($logo, PATHINFO_EXTENSION);
        $documents = !empty($request->documents) ? explode(',', $request->documents) : [];

        $rules = [
            'slider' => 'required',
            'language_id' => 'required',
            'title' => ['required', 'max:300'],
            'slug' => [
                'nullable',
                function ($attribute, $value, $fail) use ($slug) {
                    if (Portfolio::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->exists()) {
                        $fail('This URL slug is already in use — pick another.');
                    }
                }
            ],
            'client_name' => 'required|max:1000',
            // No longer collected on the form — kept nullable (not removed)
            // so old rows still validate, same precedent as 'tags' just above.
            'service_id' => 'nullable',
            // Legacy field, dropped from the redesigned form — kept nullable
            // rather than removed so old rows/imports with tags still validate.
            'tags' => 'nullable',
            'content' => 'required',
            'image' => 'required',
            'status_id' => 'required|integer',
            'serial_number' => 'required|integer',
            // Portfolio module overhaul — structured reference fields.
            'sector_id' => 'nullable|integer',
            'subsector_id' => 'nullable|integer',
            'country' => 'nullable|max:2',
            'year' => 'nullable|max:10',
            'partners' => 'nullable|max:255',
            'problematique' => 'nullable',
            'mission_ica' => 'nullable',
            'expertise_mobilisee' => 'nullable',
            'solution_approche' => 'nullable',
            'resultat_statut' => 'nullable',
            'impact' => 'nullable',
            'problematique_icon' => 'nullable|max:60',
            'mission_ica_icon' => 'nullable|max:60',
            'expertise_mobilisee_icon' => 'nullable|max:60',
            'solution_approche_icon' => 'nullable|max:60',
            'resultat_statut_icon' => 'nullable|max:60',
            'impact_icon' => 'nullable|max:60',
            // Carousel/hero overlay
            'overlay_title' => 'nullable|max:300',
            'overlay_subtitle' => 'nullable|max:255',
            'overlay_description' => 'nullable',
            'overlay_color' => 'nullable|regex:/^#?[0-9a-fA-F]{6}$/',
            'overlay_opacity' => 'nullable|integer|min:30|max:95',
            'overlay_bloom_opacity' => 'nullable|integer|min:30|max:95',
            'highlights_json' => 'nullable|json',
            'partner_ids' => 'nullable|array',
            'partner_ids.*' => 'integer',
            'end_date' => ['nullable', $this->endDateRule($request)],
        ];

        if ($request->filled('slider')) {
            $rules['slider'] = [
                function ($attribute, $value, $fail) use ($sliders, $allowedExts) {
                    foreach ($sliders as $key => $slider) {
                        $extSlider = pathinfo($slider, PATHINFO_EXTENSION);
                        if (!in_array($extSlider, $allowedExts)) {
                            return $fail("Only png, jpg, jpeg images are allowed");
                        }
                    }
                }
            ];
        }

        if ($request->filled('image')) {
            $rules['image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('client_logo')) {
            $rules['client_logo'] = [
                function ($attribute, $value, $fail) use ($extLogo, $allowedExts) {
                    if (!in_array($extLogo, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('documents')) {
            $rules['documents'] = [
                function ($attribute, $value, $fail) use ($documents) {
                    foreach ($documents as $document) {
                        $extDoc = strtolower(pathinfo($document, PATHINFO_EXTENSION));
                        if (!in_array($extDoc, self::ALLOWED_DOCUMENT_EXTS)) {
                            return $fail("Only pdf, doc, docx, xlsx documents are allowed");
                        }
                    }
                }
            ];
        }

        $messages = [
            'language_id.required' => 'The language field is required',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $in = $request->all();
        $in['language_id'] = $request->language_id;
        $in['slug'] = $slug;
        $in['content'] = str_replace(url('/') . '/assets/front/img/', "{base_url}/assets/front/img/", clean($request->content));
        // Now a select (Published/Unpublished), not a checkbox — always
        // submits a real value, so read it directly instead of filled().
        $in['is_published'] = $request->is_published == 1 ? 1 : 0;
        // Native <input type="color"> always submits a leading '#' —
        // stored without one (see the migration's own comment).
        $in['overlay_color'] = $request->filled('overlay_color') ? ltrim($request->overlay_color, '#') : null;
        // end_date is a real `date` column — see parseAdminDate()'s comment.
        // Validated as parseable above, so this is safe to trust here.
        $in['end_date'] = $request->filled('end_date') ? $this->parseAdminDate($request->end_date)->format('Y-m-d') : null;

        if ($request->filled('image')) {
            $filename = uniqid() . '.' . $extImage;
            @copy($image, FRONT_IMG_PATH . self::FEATURED_SUBDIR . $filename);
            $in['featured_image'] = $filename;
        }

        if ($request->filled('client_logo')) {
            @mkdir(FRONT_IMG_PATH . self::LOGO_SUBDIR, 0775, true);
            $filename = uniqid() . '.' . $extLogo;
            @copy($logo, FRONT_IMG_PATH . self::LOGO_SUBDIR . $filename);
            $in['client_logo'] = $filename;
        }

        $portfolio = Portfolio::create($in);
        clear_slug_redirect('portfolio', $slug);

        // The Portfolios listing page (/portfolios) is Cloudflare-edge-
        // cached (see routes/web.php's $cfCacheableTypes) — a new/renamed
        // portfolio's link on it otherwise wouldn't show/update until that
        // cache's own TTL expires. Same call TenderController already
        // makes after its own store()/update() for the identical reason.
        CloudflareController::purge();

        foreach ($sliders as $key => $slider) {
            $extSlider = pathinfo($slider, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extSlider;
            @copy($slider, FRONT_IMG_PATH . self::SLIDER_SUBDIR . $filename);

            $pi = new PortfolioImage;
            $pi->portfolio_id = $portfolio->id;
            $pi->image = $filename;
            $pi->save();
        }

        $this->storeDocuments($portfolio, $documents);
        $this->storeHighlights($portfolio, $request->highlights_json);
        $portfolio->partnerRefs()->sync($request->input('partner_ids', []));

        Session::flash('success', 'Portfolio added successfully!');
        return "success";
    }

    /** Copies LFM-selected document paths into storage and records them, mirroring the slider-image handling above. */
    private function storeDocuments(Portfolio $portfolio, array $documents): void
    {
        if (empty($documents)) {
            return;
        }

        $dir = FRONT_IMG_PATH . self::DOCUMENT_SUBDIR;
        @mkdir($dir, 0775, true);

        foreach ($documents as $document) {
            $originalName = basename($document);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);

            // Kept as the visitor-facing download name (see original_name
            // below) — this is only what lands on disk, so it just needs
            // to be a safe filesystem name, not a pretty one.
            $safeBase = preg_replace('/[^A-Za-z0-9_\-]+/', '-', $baseName) ?: 'document';

            // Two portfolios (or two uploads) can genuinely share a
            // filename — "brochure.pdf" is a common one — so a second
            // upload can't just reuse the first's disk name outright.
            // Keeping the requested name AND staying collision-free means
            // appending a numeric suffix once a name is already taken,
            // same idea as how a browser handles a repeat download name.
            $filename = $safeBase . '.' . $ext;
            $i = 1;
            while (file_exists($dir . $filename)) {
                $filename = $safeBase . '-' . $i . '.' . $ext;
                $i++;
            }

            $fullPath = $dir . $filename;
            @copy($document, $fullPath);

            $pd = new PortfolioDocument();
            $pd->portfolio_id = $portfolio->id;
            $pd->file = $filename;
            $pd->original_name = $originalName;
            $pd->size = @filesize($fullPath) ?: null;
            $pd->save();
        }
    }

    public function images($portid)
    {
        $images = PortfolioImage::select('image')->where('portfolio_id', $portid)->get();
        $convImages = [];

        foreach ($images as $key => $image) {
            $convImages[] = url(FRONT_IMG_PATH . "portfolios/sliders/$image->image");
        }

        return $convImages;
    }

    /** Existing documents, pre-seeding the Documents multi-picker on edit — mirrors images() above. */
    public function documents($portid)
    {
        $documents = PortfolioDocument::where('portfolio_id', $portid)->get();
        $convDocs = [];

        foreach ($documents as $document) {
            $convDocs[] = url(FRONT_IMG_PATH . self::DOCUMENT_SUBDIR . $document->file);
        }

        return $convDocs;
    }

    public function update(Request $request)
    {
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $portfolioId = $request->portfolio_id;
        $oldSlug = $portfolio->slug;

        // Editable slug, preserved across title edits: an explicit `slug`
        // field wins; otherwise KEEP the existing slug as-is — do NOT
        // recompute from title (that was the old behavior, and it silently
        // broke every previously-shared/indexed link on every save).
        $portfolioSlugExists = fn ($s) => Portfolio::whereRaw('LOWER(slug) = ?', [strtolower($s)])->where('id', '!=', $portfolioId)->exists();

        // "Regenerate URL" checkbox wins over everything else — same smart
        // process as create, run against the (possibly just-edited) title,
        // discarding whatever's in the Slug field.
        if ($request->boolean('regenerate_slug')) {
            $slug = unique_intelligent_slug($request->title, $portfolioSlugExists);
        } else {
            $slug = $request->filled('slug')
                ? make_slug($request->slug)
                : ($oldSlug ?: unique_intelligent_slug($request->title, $portfolioSlugExists));
        }

        $sliders = !empty($request->slider) ? explode(',', $request->slider) : [];
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);
        $logo = $request->client_logo;
        $extLogo = pathinfo($logo, PATHINFO_EXTENSION);
        $documents = !empty($request->documents) ? explode(',', $request->documents) : [];

        $rules = [
            'slider' => 'required',
            'title' => ['required', 'max:300'],
            'slug' => [
                'nullable',
                function ($attribute, $value, $fail) use ($slug, $portfolioId) {
                    if (Portfolio::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->where('id', '!=', $portfolioId)->exists()) {
                        $fail('This URL slug is already in use — pick another.');
                    }
                }
            ],
            'client_name' => 'required|max:1000',
            // No longer collected on the form — kept nullable (not removed)
            // so old rows still validate, same precedent as 'tags' just above.
            'service_id' => 'nullable',
            'tags' => 'nullable',
            'content' => 'required',
            'status_id' => 'required|integer',
            'serial_number' => 'required|integer',
            'sector_id' => 'nullable|integer',
            'subsector_id' => 'nullable|integer',
            'country' => 'nullable|max:2',
            'year' => 'nullable|max:10',
            'partners' => 'nullable|max:255',
            'problematique' => 'nullable',
            'mission_ica' => 'nullable',
            'expertise_mobilisee' => 'nullable',
            'solution_approche' => 'nullable',
            'resultat_statut' => 'nullable',
            'impact' => 'nullable',
            'problematique_icon' => 'nullable|max:60',
            'mission_ica_icon' => 'nullable|max:60',
            'expertise_mobilisee_icon' => 'nullable|max:60',
            'solution_approche_icon' => 'nullable|max:60',
            'resultat_statut_icon' => 'nullable|max:60',
            'impact_icon' => 'nullable|max:60',
            // Carousel/hero overlay
            'overlay_title' => 'nullable|max:300',
            'overlay_subtitle' => 'nullable|max:255',
            'overlay_description' => 'nullable',
            'overlay_color' => 'nullable|regex:/^#?[0-9a-fA-F]{6}$/',
            'overlay_opacity' => 'nullable|integer|min:30|max:95',
            'overlay_bloom_opacity' => 'nullable|integer|min:30|max:95',
            'highlights_json' => 'nullable|json',
            'partner_ids' => 'nullable|array',
            'partner_ids.*' => 'integer',
            'end_date' => ['nullable', $this->endDateRule($request)],
        ];

        if ($request->filled('image')) {
            $rules['image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('slider')) {
            $rules['slider'] = [
                function ($attribute, $value, $fail) use ($sliders, $allowedExts) {
                    foreach ($sliders as $key => $slider) {
                        $extSlider = pathinfo($slider, PATHINFO_EXTENSION);
                        if (!in_array($extSlider, $allowedExts)) {
                            return $fail("Only png, jpg, jpeg images are allowed");
                        }
                    }
                }
            ];
        }

        if ($request->filled('client_logo')) {
            $rules['client_logo'] = [
                function ($attribute, $value, $fail) use ($extLogo, $allowedExts) {
                    if (!in_array($extLogo, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        if ($request->filled('documents')) {
            $rules['documents'] = [
                function ($attribute, $value, $fail) use ($documents) {
                    foreach ($documents as $document) {
                        $extDoc = strtolower(pathinfo($document, PATHINFO_EXTENSION));
                        if (!in_array($extDoc, self::ALLOWED_DOCUMENT_EXTS)) {
                            return $fail("Only pdf, doc, docx, xlsx documents are allowed");
                        }
                    }
                }
            ];
        }

        $messages = [];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $in = $request->all();
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $in['content'] = str_replace(url('/') . '/assets/front/img/', "{base_url}/assets/front/img/", clean($request->content));
        $in['slug'] = $slug;
        // Now a select (Published/Unpublished), not a checkbox — always
        // submits a real value, so read it directly instead of filled().
        $in['is_published'] = $request->is_published == 1 ? 1 : 0;
        $in['overlay_color'] = $request->filled('overlay_color') ? ltrim($request->overlay_color, '#') : null;
        // end_date is a real `date` column — see parseAdminDate()'s comment.
        // Validated as parseable above, so this is safe to trust here.
        $in['end_date'] = $request->filled('end_date') ? $this->parseAdminDate($request->end_date)->format('Y-m-d') : null;

        if ($request->filled('image')) {
            @unlink(FRONT_IMG_PATH . self::FEATURED_SUBDIR . $portfolio->featured_image);
            $filename = uniqid() . '.' . $extImage;
            @copy($image, FRONT_IMG_PATH . self::FEATURED_SUBDIR . $filename);
            $in['featured_image'] = $filename;
        }

        if ($request->filled('client_logo')) {
            @mkdir(FRONT_IMG_PATH . self::LOGO_SUBDIR, 0775, true);
            if ($portfolio->client_logo) {
                @unlink(FRONT_IMG_PATH . self::LOGO_SUBDIR . $portfolio->client_logo);
            }
            $filename = uniqid() . '.' . $extLogo;
            @copy($logo, FRONT_IMG_PATH . self::LOGO_SUBDIR . $filename);
            $in['client_logo'] = $filename;
        }

        if ($slug !== $oldSlug) {
            record_slug_redirect('portfolio', $oldSlug, $portfolio->id);
            clear_slug_redirect('portfolio', $slug);
        }

        $portfolio->fill($in)->save();

        // Same reasoning as store() above — the listing page's cached HTML
        // otherwise keeps showing the old slug/title until its edge TTL
        // expires on its own.
        CloudflareController::purge();

        // copy the sliders first
        $fileNames = [];
        foreach ($sliders as $key => $slider) {
            $extSlider = pathinfo($slider, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extSlider;
            @copy($slider, FRONT_IMG_PATH . self::SLIDER_SUBDIR . $filename);
            $fileNames[] = $filename;
        }

        // delete & unlink previous slider images
        $pis = PortfolioImage::where('portfolio_id', $portfolio->id)->get();
        foreach ($pis as $key => $pi) {
            @unlink(FRONT_IMG_PATH . self::SLIDER_SUBDIR . $pi->image);
            $pi->delete();
        }

        // store new slider images
        foreach ($fileNames as $key => $fileName) {
            $pi = new PortfolioImage;
            $pi->portfolio_id = $portfolio->id;
            $pi->image = $fileName;
            $pi->save();
        }

        // Documents: same wipe-then-recreate approach as sliders above, for
        // the same reason — the picker always resubmits the full current
        // list, so a full replace stays in sync without diffing.
        $oldDocs = PortfolioDocument::where('portfolio_id', $portfolio->id)->get();
        foreach ($oldDocs as $oldDoc) {
            @unlink(FRONT_IMG_PATH . self::DOCUMENT_SUBDIR . $oldDoc->file);
            $oldDoc->delete();
        }
        $this->storeDocuments($portfolio, $documents);

        // Highlights: same wipe-then-recreate approach as sliders/documents
        // above — the drag-drop list always resubmits its full current
        // state (order included), so a full replace stays in sync without
        // diffing against what's already stored.
        PortfolioHighlight::where('portfolio_id', $portfolio->id)->delete();
        $this->storeHighlights($portfolio, $request->highlights_json);
        $portfolio->partnerRefs()->sync($request->input('partner_ids', []));

        Session::flash('success', 'Portfolio updated successfully!');
        return "success";
    }

    /**
     * Decodes the drag-drop-ordered highlights list (JSON array of
     * {icon, label}, built client-side right before submit — see
     * _form.blade.php) and (re)creates the rows in that exact order.
     */
    private function storeHighlights(Portfolio $portfolio, ?string $highlightsJson): void
    {
        if (empty($highlightsJson)) {
            return;
        }

        $items = json_decode($highlightsJson, true);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $i => $item) {
            $label = trim($item['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $highlight = new PortfolioHighlight();
            $highlight->portfolio_id = $portfolio->id;
            $highlight->icon = $item['icon'] ?? null;
            $highlight->label = $label;
            $highlight->serial_number = $i;
            $highlight->save();
        }
    }

    public function delete(Request $request)
    {
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        foreach ($portfolio->portfolio_images as $key => $pi) {
            @unlink(FRONT_IMG_PATH . self::SLIDER_SUBDIR . $pi->image);
            $pi->delete();
        }
        foreach ($portfolio->documents as $pd) {
            @unlink(FRONT_IMG_PATH . self::DOCUMENT_SUBDIR . $pd->file);
            $pd->delete();
        }
        PortfolioHighlight::where('portfolio_id', $portfolio->id)->delete();
        if ($portfolio->client_logo) {
            @unlink(FRONT_IMG_PATH . self::LOGO_SUBDIR . $portfolio->client_logo);
        }
        @unlink(FRONT_IMG_PATH . self::FEATURED_SUBDIR . $portfolio->featured_image);

        $this->deleteFromMegaMenu($portfolio);

        $portfolio->delete();

        Session::flash('success', 'Portfolio deleted successfully!');
        return back();
    }

    public function deleteFromMegaMenu($portfolio)
    {
        // unset portfolio from megamenu for service_category = 1
        $megamenu = Megamenu::where('language_id', $portfolio->language_id)->where('category', 1)->where('type', 'portfolios');
        if ($megamenu->count() > 0) {
            $megamenu = $megamenu->first();
            $menus = json_decode($megamenu->menus, true);
            if (!empty($portfolio->service) && !empty($portfolio->service->scategory)) {
                $catId = $portfolio->service->scategory->id;
                if (is_array($menus) && array_key_exists("$catId", $menus)) {
                    if (in_array($portfolio->id, $menus["$catId"])) {
                        $index = array_search($portfolio->id, $menus["$catId"]);
                        unset($menus["$catId"]["$index"]);
                        $menus["$catId"] = array_values($menus["$catId"]);
                        if (count($menus["$catId"]) == 0) {
                            unset($menus["$catId"]);
                        }
                        $megamenu->menus = json_encode($menus);
                        $megamenu->save();
                    }
                }
            }
        }

        // unset portfolio from megamenu for service_category = 0
        $megamenu = Megamenu::where('language_id', $portfolio->language_id)->where('category', 0)->where('type', 'portfolios');
        if ($megamenu->count() > 0) {
            $megamenu = $megamenu->first();
            $menus = json_decode($megamenu->menus, true);
            if (is_array($menus)) {
                if (in_array($portfolio->id, $menus)) {
                    $index = array_search($portfolio->id, $menus);
                    unset($menus["$index"]);
                    $menus = array_values($menus);
                    $megamenu->menus = json_encode($menus);
                    $megamenu->save();
                }
            }
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $portfolio = Portfolio::findOrFail($id);
            foreach ($portfolio->portfolio_images as $key => $pi) {
                @unlink(FRONT_IMG_PATH . self::SLIDER_SUBDIR . $pi->image);
                $pi->delete();
            }
            foreach ($portfolio->documents as $pd) {
                @unlink(FRONT_IMG_PATH . self::DOCUMENT_SUBDIR . $pd->file);
                $pd->delete();
            }
        }

        foreach ($ids as $id) {
            $portfolio = Portfolio::findOrFail($id);
            if ($portfolio->client_logo) {
                @unlink(FRONT_IMG_PATH . self::LOGO_SUBDIR . $portfolio->client_logo);
            }
            @unlink(FRONT_IMG_PATH . self::FEATURED_SUBDIR . $portfolio->featured_image);

            $this->deleteFromMegaMenu($portfolio);

            $portfolio->delete();
        }

        Session::flash('success', 'Portfolios deleted successfully!');
        return "success";
    }

    public function getservices($langid)
    {
        $services = Service::where('language_id', $langid)->get();

        return $services;
    }

    /**
     * Resolves the identity-card partial's supporting variables (client
     * logo URL, country name, gallery/document lists) for a REAL, saved
     * portfolio — used by show() (read-only eye modal). preview() below
     * builds the equivalent from raw, unsaved form fields instead.
     */
    private function identityCardDataFor(Portfolio $portfolio): array
    {
        $countryName = null;
        if ($portfolio->country) {
            foreach (Countries::all() as $c) {
                if ($c['iso'] === $portfolio->country) {
                    $countryName = $c['name'];
                    break;
                }
            }
        }

        return [
            'portfolio' => $portfolio,
            'heroImageUrl' => $portfolio->featured_image ? url(FRONT_IMG_PATH . self::FEATURED_SUBDIR . $portfolio->featured_image) : null,
            'clientLogoUrl' => $portfolio->client_logo ? url(FRONT_IMG_PATH . self::LOGO_SUBDIR . $portfolio->client_logo) : null,
            'countryName' => $countryName,
            'galleryImages' => $portfolio->portfolio_images->map(fn($pi) => url(FRONT_IMG_PATH . self::SLIDER_SUBDIR . $pi->image))->all(),
            'documentList' => $portfolio->documents->map(fn($pd) => [
                'name' => $pd->original_name ?: $pd->file,
                'url' => url(FRONT_IMG_PATH . self::DOCUMENT_SUBDIR . $pd->file),
                'size' => $pd->size,
            ])->all(),
        ];
    }

    /**
     * Read-only "eye" modal — a dedicated admin-styled, tabbed data review
     * (Overview / Evidence of Competence / Carousel Overlay / Media),
     * deliberately its OWN view rather than the shared preview_content
     * partial: that partial exists specifically to render the real
     * front-end dark-glass markup so the "Aperçu" preview (below) is a
     * true WYSIWYG match for what gets published — admin chrome here
     * would defeat that purpose there. This modal's job is the opposite:
     * reviewing the record's data as an admin, not previewing the page.
     * Approved design: https://claude.ai/code/artifact/67559e95-7b57-4b4e-b0cc-00ebade4a20c
     */
    public function show($id)
    {
        $portfolio = Portfolio::with(['sector', 'subsector', 'statusInfo', 'service', 'portfolio_images', 'documents', 'highlights', 'partnerRefs'])->findOrFail($id);
        if (!empty($portfolio->language)) {
            app()->setLocale($portfolio->language->code);
        }

        return view('admin.portfolio.details_modal', array_merge(
            $this->identityCardDataFor($portfolio),
            ['bex' => BasicExtra::first()]
        ));
    }

    /**
     * "Aperçu" preview — renders the CURRENT, UNSAVED form fields through the
     * same shared partial the real page and the eye modal use, so what an
     * admin sees here is guaranteed to match what gets published (per the
     * doc's "publish with confidence" point). Nothing here touches the DB.
     */
    public function preview(Request $request)
    {
        if ($request->filled('language_id')) {
            $lang = Language::find($request->language_id);
            if ($lang) {
                app()->setLocale($lang->code);
            }
        }

        $sector = $request->filled('sector_id') ? PortfolioSector::find($request->sector_id) : null;
        $subsector = $request->filled('subsector_id') ? PortfolioSector::find($request->subsector_id) : null;
        $statusInfo = $request->filled('status_id') ? PortfolioStatus::find($request->status_id) : null;

        $countryName = null;
        if ($request->filled('country')) {
            foreach (Countries::all() as $c) {
                if ($c['iso'] === $request->country) {
                    $countryName = $c['name'];
                    break;
                }
            }
        }

        $portfolio = (object) [
            'title' => $request->title,
            'client_name' => $request->client_name,
            'sector' => $sector,
            'subsector' => $subsector,
            'country' => $request->country,
            'statusInfo' => $statusInfo,
            'partnerRefs' => Partner::whereIn('id', $request->input('partner_ids', []))->get(),
            'problematique' => $request->problematique,
            'mission_ica' => $request->mission_ica,
            'expertise_mobilisee' => $request->expertise_mobilisee,
            'solution_approche' => $request->solution_approche,
            'resultat_statut' => $request->resultat_statut,
            'impact' => $request->impact,
            'problematique_icon' => $request->problematique_icon,
            'mission_ica_icon' => $request->mission_ica_icon,
            'expertise_mobilisee_icon' => $request->expertise_mobilisee_icon,
            'solution_approche_icon' => $request->solution_approche_icon,
            'resultat_statut_icon' => $request->resultat_statut_icon,
            'impact_icon' => $request->impact_icon,
        ];

        $galleryImages = [];
        if ($request->filled('slider')) {
            foreach (explode(',', $request->slider) as $path) {
                $galleryImages[] = url($path);
            }
        }

        $documentList = [];
        if ($request->filled('documents')) {
            foreach (explode(',', $request->documents) as $path) {
                $documentList[] = [
                    'name' => basename($path),
                    'url' => url($path),
                    'size' => @filesize(public_path($path)) ?: null,
                ];
            }
        }

        return view('admin.portfolio.preview_content', [
            'portfolio' => $portfolio,
            'heroImageUrl' => $request->filled('image') ? url($request->image) : null,
            'clientLogoUrl' => $request->filled('client_logo') ? url($request->client_logo) : null,
            'countryName' => $countryName,
            'galleryImages' => $galleryImages,
            'documentList' => $documentList,
        ]);
    }

    /** Deep-copies a portfolio (fillable fields + gallery + documents) as a new, unpublished draft. */
    public function duplicate($id)
    {
        $original = Portfolio::with(['portfolio_images', 'documents'])->findOrFail($id);

        $copy = $original->replicate();
        $copy->title = $original->title . ' (Copie)';
        $copy->slug = make_slug($copy->title) . '-' . uniqid();
        $copy->is_published = 0;
        $copy->save();

        foreach ($original->portfolio_images as $pi) {
            $newFilename = uniqid() . '.' . pathinfo($pi->image, PATHINFO_EXTENSION);
            @copy(FRONT_IMG_PATH . self::SLIDER_SUBDIR . $pi->image, FRONT_IMG_PATH . self::SLIDER_SUBDIR . $newFilename);
            // PortfolioImage has no $fillable (matches store()/update() above,
            // which never mass-assign it either) — plain property assignment.
            $newPi = new PortfolioImage();
            $newPi->portfolio_id = $copy->id;
            $newPi->image = $newFilename;
            $newPi->save();
        }

        foreach ($original->documents as $pd) {
            $newFilename = uniqid() . '.' . pathinfo($pd->file, PATHINFO_EXTENSION);
            @copy(FRONT_IMG_PATH . self::DOCUMENT_SUBDIR . $pd->file, FRONT_IMG_PATH . self::DOCUMENT_SUBDIR . $newFilename);
            PortfolioDocument::create(['portfolio_id' => $copy->id, 'file' => $newFilename, 'original_name' => $pd->original_name, 'size' => $pd->size]);
        }

        if ($original->featured_image) {
            $newFilename = uniqid() . '.' . pathinfo($original->featured_image, PATHINFO_EXTENSION);
            @copy(FRONT_IMG_PATH . self::FEATURED_SUBDIR . $original->featured_image, FRONT_IMG_PATH . self::FEATURED_SUBDIR . $newFilename);
            $copy->featured_image = $newFilename;
        }
        if ($original->client_logo) {
            $newFilename = uniqid() . '.' . pathinfo($original->client_logo, PATHINFO_EXTENSION);
            @copy(FRONT_IMG_PATH . self::LOGO_SUBDIR . $original->client_logo, FRONT_IMG_PATH . self::LOGO_SUBDIR . $newFilename);
            $copy->client_logo = $newFilename;
        }
        $copy->save();

        Session::flash('success', 'Portfolio duplicated successfully!');
        return redirect()->route('admin.portfolio.edit', $copy->id);
    }

    /** Publier / Dépublier — independent of `status` (project stage), controls front-end visibility only. */
    public function toggleVisibility(Request $request)
    {
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $portfolio->is_published = $request->is_published;
        $portfolio->save();

        return response()->json(['success' => true, 'is_published' => $portfolio->is_published]);
    }

    /** "Changer le statut" quick action from the list's 3-dot menu — no full form reload. */
    public function updateStatus(Request $request)
    {
        $request->validate(['status_id' => 'required|integer|exists:portfolio_statuses,id']);

        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $portfolio->status_id = $request->status_id;
        $portfolio->save();

        return response()->json([
            'success' => true,
            'status_id' => $portfolio->status_id,
            'status_name' => convertUtf8($portfolio->statusInfo->name ?? ''),
        ]);
    }

    /** Archiver — hides from both the default admin list and the frontend, independent of is_published. */
    public function toggleArchive(Request $request)
    {
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $portfolio->is_archived = $request->is_archived;
        $portfolio->save();

        return response()->json(['success' => true, 'is_archived' => $portfolio->is_archived]);
    }

    public function export(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $query = Portfolio::with(['sector', 'subsector', 'statusInfo'])->where('language_id', $lang->id ?? 0);

        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        return Excel::download(new PortfolioExport($query->get()), 'portfolios.csv');
    }

    public function feature(Request $request)
    {
        $portfolio = Portfolio::find($request->portfolio_id);
        $portfolio->feature = $request->feature;
        $portfolio->save();

        if ($request->feature == 1) {
            Session::flash('success', 'Featured successfully!');
        } else {
            Session::flash('success', 'Unfeatured successfully!');
        }

        return back();
    }

    public function settings(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['bsData'] = BasicSetting::where('language_id', $lang->id)->first();
        // Gallery autoplay speed is global (not per-language), same as the
        // homepage carousel speeds in admin/home/sections.
        $data['abe'] = BasicExtended::first();

        return view('admin.portfolio.settings', $data);
    }

    public function updateSettings(Request $request, $langid)
    {
        $request->validate([
            'portfolio_breadcrumb_overlay_color' => 'nullable|max:20',
            'portfolio_breadcrumb_overlay_opacity' => 'nullable|numeric|min:0|max:1',
            'portfolio_details_gallery_speed' => 'nullable|integer|min:2000|max:30000',
        ]);

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->portfolio_breadcrumb_overlay_color = $request->portfolio_breadcrumb_overlay_color;
        $bs->portfolio_breadcrumb_overlay_opacity = $request->portfolio_breadcrumb_overlay_opacity;

        if ($request->filled('portfolio_breadcrumb_bg')) {
            $allowedExts = allowed_image_extensions();
            $extBg = pathinfo($request->portfolio_breadcrumb_bg, PATHINFO_EXTENSION);
            if (in_array($extBg, $allowedExts)) {
                @unlink(FRONT_IMG_PATH . $bs->portfolio_breadcrumb_bg);
                $filename = uniqid() . '.' . $extBg;
                @copy($request->portfolio_breadcrumb_bg, FRONT_IMG_PATH . $filename);
                $bs->portfolio_breadcrumb_bg = $filename;
            }
        }

        $bs->save();

        // Global field, saved on every language row (mirrors how the
        // homepage carousel speeds are saved in BasicController::updatesections).
        $bes = BasicExtended::all();
        foreach ($bes as $be) {
            $be->portfolio_details_gallery_speed = $request->filled('portfolio_details_gallery_speed')
                ? (int) $request->portfolio_details_gallery_speed
                : 7000;
            $be->save();
        }

        $lang = Language::find($langid);

        Session::flash('success', 'Settings updated successfully.');

        return redirect()->route('admin.portfolio.settings', ['language' => $lang->code]);
    }

    public function deleteBreadcrumbBg($langid)
    {
        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();

        if ($bs && $bs->portfolio_breadcrumb_bg) {
            @unlink(FRONT_IMG_PATH . $bs->portfolio_breadcrumb_bg);
            $bs->portfolio_breadcrumb_bg = null;
            $bs->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
