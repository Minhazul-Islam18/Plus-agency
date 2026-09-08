<?php

namespace App\Http\Controllers\Admin;

use App\Service;
use App\Language;
use App\Megamenu;
use App\Portfolio;
use App\BasicSetting;
use App\BasicExtended;
use App\PortfolioImage;
use App\PortfolioSector;
use App\PortfolioStatus;
use App\PortfolioDocument;
use App\Http\Helpers\Countries;
use App\Exports\PortfolioExport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PortfolioController extends Controller
{
    private const SLIDER_SUBDIR = 'portfolios/sliders/';
    private const FEATURED_SUBDIR = 'portfolios/featured/';
    private const LOGO_SUBDIR = 'portfolios/logos/';
    private const DOCUMENT_SUBDIR = 'portfolios/documents/';

    /** Extensions accepted by the Documents picker (LFM "file" category — see mockup: PDF, DOC, DOCX, XLSX). */
    private const ALLOWED_DOCUMENT_EXTS = ['pdf', 'doc', 'docx', 'xlsx'];

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

        $data['portfolios'] = $query->with('statusInfo')->orderBy('id', 'DESC')->get();
        $data['sectors'] = PortfolioSector::where('language_id', $lang_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
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
        $data['sectors'] = PortfolioSector::where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['statuses'] = PortfolioStatus::where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['countries'] = Countries::all();
        $data['tportfolios'] = Portfolio::where('language_id', 0)->get();
        return view('admin.portfolio.create', $data);
    }

    public function edit($id)
    {
        $data['portfolio'] = Portfolio::findOrFail($id);
        if (!empty($data['portfolio']->language)) {
            app()->setLocale($data['portfolio']->language->code);
        }
        $data['services'] = Service::where('language_id', $data['portfolio']->language_id)->get();
        $data['sectors'] = PortfolioSector::where('language_id', $data['portfolio']->language_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
        $data['statuses'] = PortfolioStatus::where('language_id', $data['portfolio']->language_id)->where('status', 1)->orderBy('serial_number', 'asc')->get();
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
        $slug = make_slug($request->title);

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
            'title' => [
                'required',
                'max:300',
                function ($attribute, $value, $fail) use ($slug) {
                    if (Portfolio::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->exists()) {
                        $fail('The title field must be unique.');
                    }
                }
            ],
            'client_name' => 'required|max:1000',
            'service_id' => 'required',
            // Legacy field, dropped from the redesigned form — kept nullable
            // rather than removed so old rows/imports with tags still validate.
            'tags' => 'nullable',
            'content' => 'required',
            'image' => 'required',
            'status_id' => 'required|integer',
            'serial_number' => 'required|integer',
            // Portfolio module overhaul — structured reference fields.
            'sector_id' => 'nullable|integer',
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
            'service_id.required' => 'service is required'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $in = $request->all();
        $in['language_id'] = $request->language_id;
        $in['slug'] = $slug;
        $in['content'] = clean(str_replace(url('/') . '/assets/front/img/', "{base_url}/assets/front/img/", $request->content));
        // Now a select (Published/Unpublished), not a checkbox — always
        // submits a real value, so read it directly instead of filled().
        $in['is_published'] = $request->is_published == 1 ? 1 : 0;

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
        $slug = make_slug($request->title);
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $portfolioId = $request->portfolio_id;

        $sliders = !empty($request->slider) ? explode(',', $request->slider) : [];
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);
        $logo = $request->client_logo;
        $extLogo = pathinfo($logo, PATHINFO_EXTENSION);
        $documents = !empty($request->documents) ? explode(',', $request->documents) : [];

        $rules = [
            'slider' => 'required',
            'title' => [
                'required',
                'max:300',
                function ($attribute, $value, $fail) use ($slug, $portfolioId) {
                    if (Portfolio::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->where('id', '!=', $portfolioId)->exists()) {
                        $fail('The title field must be unique.');
                    }
                }
            ],
            'client_name' => 'required|max:1000',
            'service_id' => 'required',
            'tags' => 'nullable',
            'content' => 'required',
            'status_id' => 'required|integer',
            'serial_number' => 'required|integer',
            'sector_id' => 'nullable|integer',
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

        $messages = [
            'service_id.required' => 'service is required'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $in = $request->all();
        $portfolio = Portfolio::findOrFail($request->portfolio_id);
        $in['content'] = clean(str_replace(url('/') . '/assets/front/img/', "{base_url}/assets/front/img/", $request->content));
        $in['slug'] = $slug;
        // Now a select (Published/Unpublished), not a checkbox — always
        // submits a real value, so read it directly instead of filled().
        $in['is_published'] = $request->is_published == 1 ? 1 : 0;

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

        $portfolio->fill($in)->save();

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

        Session::flash('success', 'Portfolio updated successfully!');
        return "success";
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

    /** Read-only "eye" modal — renders the real saved portfolio through the shared partial. */
    public function show($id)
    {
        $portfolio = Portfolio::with(['sector', 'statusInfo', 'portfolio_images', 'documents'])->findOrFail($id);
        if (!empty($portfolio->language)) {
            app()->setLocale($portfolio->language->code);
        }

        return view('admin.portfolio.preview_content', $this->identityCardDataFor($portfolio));
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
            'country' => $request->country,
            'statusInfo' => $statusInfo,
            'partners' => $request->partners,
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
        $query = Portfolio::with(['sector', 'statusInfo'])->where('language_id', $lang->id ?? 0);

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
            'portfolio_details_gallery_speed' => 'nullable|integer|min:2000|max:15000',
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
