<?php

namespace App\Http\Controllers\Admin;

use Session;
use Validator;
use App\Service;
use App\Language;
use App\Megamenu;
use App\Scategory;
use App\BasicExtra;
use App\BasicSetting;
use App\BasicExtended;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    private const IMG_SUBDIR = 'services/';

    public function settings(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abex'] = BasicExtra::first();
        $data['bsData'] = BasicSetting::where('language_id', $lang->id)->first();

        return view('admin.service.settings', $data);
    }

    public function updateSettings(Request $request, $langid)
    {
        $request->validate([
            'service_breadcrumb_overlay_color' => 'nullable|max:20',
            'service_breadcrumb_overlay_opacity' => 'nullable|numeric|min:0|max:1',
        ]);

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->service_breadcrumb_overlay_color = $request->service_breadcrumb_overlay_color;
        $bs->service_breadcrumb_overlay_opacity = $request->service_breadcrumb_overlay_opacity;

        if ($request->filled('service_breadcrumb_bg')) {
            $allowedExts = allowed_image_extensions();
            $extBg = pathinfo($request->service_breadcrumb_bg, PATHINFO_EXTENSION);
            if (in_array($extBg, $allowedExts)) {
                @unlink(FRONT_IMG_PATH . $bs->service_breadcrumb_bg);
                $filename = uniqid() . '.' . $extBg;
                @copy($request->service_breadcrumb_bg, FRONT_IMG_PATH . $filename);
                $bs->service_breadcrumb_bg = $filename;
            }
        }

        $bs->save();

        $bexs = BasicExtra::all();
        foreach ($bexs as $bex) {
            $bex->service_category = $request->service_category;
            $bex->save();
        }
        $lang = Language::find($langid);

        $request->session()->flash('success', 'Settings updated successfully!');
        return redirect()->route('admin.service.settings', ['language' => $lang->code]);
    }

    public function deleteBreadcrumbBg($langid)
    {
        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();

        if ($bs && $bs->service_breadcrumb_bg) {
            @unlink(FRONT_IMG_PATH . $bs->service_breadcrumb_bg);
            $bs->service_breadcrumb_bg = null;
            $bs->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();

        $lang_id = $lang->id;
        $query = Service::where('language_id', $lang_id);

        if (serviceCategory() && $request->filled('category_id')) {
            $query->where('scategory_id', $request->category_id);
        }

        $data['services'] = $query->orderBy('id', 'DESC')->get();

        $data['lang_id'] = $lang_id;
        $data['abe'] = BasicExtended::where('language_id', $lang_id)->first();

        if (serviceCategory()) {
            // Every category for this language, not just active ones — an
            // admin filtering the list should be able to find services
            // under a category they've since disabled, not have it hidden.
            $data['scategories'] = Scategory::where('language_id', $lang_id)->orderBy('serial_number', 'ASC')->get();
        }

        return view('admin.service.service.index', $data);
    }

    public function edit($id)
    {
        $data['service'] = Service::findOrFail($id);
        $data['ascats'] = Scategory::where('status', 1)->where('language_id', $data['service']->language_id)->get();
        $data['abe'] = BasicExtended::where('language_id', $data['service']->language_id)->first();
        return view('admin.service.service.edit', $data);
    }

    public function store(Request $request)
    {
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $language = Language::find($request->language_id);
        $be = $language->basic_extended;

        $messages = [
            'language_id.required' => 'The language field is required'
        ];

        $slug = make_slug($request->title);

        $rules = [
            'language_id' => 'required',
            'image' => 'required',
            'title' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($slug) {
                    if (Service::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->exists()) {
                        $fail('The title field must be unique.');
                    }
                }
            ],
            'serial_number' => 'required',
            'content' => 'required',
            'details_page_status' => 'required',
            'summary' => 'required',
            'sidebar' => 'required',
            'feature' => 'required',
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

        // if 'theme version'contains service category
        if (serviceCategory()) {
            $rules["category"] = 'required';
        }

        // if 'theme version' doesn't contain service category
        if ($request->details_page_status == 0) {
            $rules["content"] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $service = new Service;
        $service->language_id = $request->language_id;
        $service->title = $request->title;

        if ($request->filled('image')) {
            $filename = uniqid() . '.' . $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $service->main_image = $filename;
        }

        $service->slug = $slug;
        // if 'theme version'contains service category
        if (serviceCategory()) {
            $service->scategory_id = $request->category;
        }
        $service->summary = $request->summary;
        $service->details_page_status = $request->details_page_status;
        $service->sidebar = $request->sidebar;
        $service->feature = $request->feature;
        $service->meta_description = $request->meta_description;
        $service->meta_keywords = $request->meta_keywords;
        $service->serial_number = $request->serial_number;
        $service->content = str_replace(url('/') . '/assets/front/img/', "{base_url}/assets/front/img/", clean($request->content));
        $service->save();

        \App\Http\Controllers\Admin\CloudflareController::purge();
        Session::flash('success', 'Service added successfully!');
        return "success";
    }

    public function update(Request $request)
    {
        $slug = make_slug($request->title);
        $service = Service::findOrFail($request->service_id);
        $serviceId = $request->service_id;

        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $language = Language::find($service->language_id);
        $be = $language->basic_extended;

        $rules = [
            'title' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) use ($slug, $serviceId) {
                    if (Service::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->where('id', '!=', $serviceId)->exists()) {
                        $fail('The title field must be unique.');
                    }
                }
            ],
            'content' => 'required',
            'serial_number' => 'required',
            'details_page_status' => 'required',
            'summary' => 'required',
            'sidebar' => 'required',
            'feature' => 'required',
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

        if (serviceCategory()) {
            $rules["category"] = 'required';
        }

        if ($request->details_page_status == 0) {
            $rules["content"] = 'nullable';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $service->title = $request->title;
        $service->slug = $slug;
        if (serviceCategory()) {
            $service->scategory_id = $request->category;
        }
        $service->summary = $request->summary;
        $service->details_page_status = $request->details_page_status;
        $service->sidebar = $request->sidebar;
        $service->feature = $request->feature;
        $service->serial_number = $request->serial_number;
        $service->meta_keywords = $request->meta_keywords;
        $service->meta_description = $request->meta_description;
        $service->content = str_replace(url('/') . '/assets/front/img/', "{base_url}/assets/front/img/", clean($request->content));

        if ($request->filled('image')) {
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $service->main_image);
            $filename = uniqid() . '.' . $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $service->main_image = $filename;
        }

        // Legacy rows predate the timestamps columns, so created_at is NULL
        // and the list's Published column showed "—" forever. First save
        // after this stamps it, so the date appears as soon as an article
        // is edited (and stays put on later edits).
        if (empty($service->created_at)) {
            $service->created_at = now();
        }

        $service->save();

        \App\Http\Controllers\Admin\CloudflareController::purge();
        Session::flash('success', 'Service updated successfully!');
        return "success";
    }

    public function deleteFromMegaMenu($service)
    {
        // unset service from megamenu for service_category = 1
        $megamenu = Megamenu::where('language_id', $service->language_id)->where('category', 1)->where('type', 'services');
        if ($megamenu->count() > 0) {
            $megamenu = $megamenu->first();
            $menus = json_decode($megamenu->menus, true);
            $catId = $service->scategory->id;
            if (is_array($menus) && array_key_exists("$catId", $menus)) {
                if (in_array($service->id, $menus["$catId"])) {
                    $index = array_search($service->id, $menus["$catId"]);
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

        // unset service from megamenu for service_category = 0
        $megamenu = Megamenu::where('language_id', $service->language_id)->where('category', 0)->where('type', 'services');
        if ($megamenu->count() > 0) {
            $megamenu = $megamenu->first();
            $menus = json_decode($megamenu->menus, true);
            if (is_array($menus)) {
                if (in_array($service->id, $menus)) {
                    $index = array_search($service->id, $menus);
                    unset($menus["$index"]);
                    $menus = array_values($menus);
                    $megamenu->menus = json_encode($menus);
                    $megamenu->save();
                }
            }
        }
    }

    public function delete(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $service->main_image);

        $this->deleteFromMegaMenu($service);

        $service->delete();

        Session::flash('success', 'Service deleted successfully!');
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $service = Service::findOrFail($id);
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $service->main_image);

            $this->deleteFromMegaMenu($service);

            $service->delete();
        }

        Session::flash('success', 'Services deleted successfully!');
        return "success";
    }

    public function getcats($langid)
    {
        $scategories = Scategory::where('language_id', $langid)->get();

        return $scategories;
    }

    public function feature(Request $request)
    {
        $service = Service::find($request->service_id);
        $service->feature = $request->feature;
        $service->save();

        \App\Http\Controllers\Admin\CloudflareController::purge();

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Request $request)
    {
        $service = Service::findOrFail($request->service_id);
        $service->status = $request->status == 1 ? 1 : 0;
        // Activation isn't an edit of the article's content — leave the
        // Published/updated timestamps alone.
        $service->timestamps = false;
        $service->save();

        // Cached public pages would otherwise keep serving (or missing) it.
        \App\Http\Controllers\Admin\CloudflareController::purge();

        return response()->json(['success' => true]);
    }

    public function sidebar(Request $request)
    {
        $service = Service::find($request->service_id);
        $service->sidebar = $request->sidebar;
        $service->save();

        if ($request->sidebar == 1) {
            Session::flash('success', 'Enabled successfully!');
        } else {
            Session::flash('success', 'Disabled successfully!');
        }

        return back();
    }
}
