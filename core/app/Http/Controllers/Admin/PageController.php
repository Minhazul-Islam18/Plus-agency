<?php

namespace App\Http\Controllers\Admin;


use App\BasicExtra;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Page;
use App\Language;
use Session;
use Validator;

class PageController extends Controller
{
    private const IMG_SUBDIR = 'pages/';

    /** Saves a breadcrumb_image (LFM path) into the pages img dir; returns the new filename, or null if none/invalid. */
    private function saveBreadcrumbImage(Request $request): ?string
    {
        if (!$request->filled('breadcrumb_image')) {
            return null;
        }

        $allowedExts = array('jpg', 'png', 'jpeg', 'webp', 'avif');
        $extBg = pathinfo($request->breadcrumb_image, PATHINFO_EXTENSION);
        if (!in_array(strtolower($extBg), $allowedExts)) {
            return null;
        }

        $directory = FRONT_IMG_PATH . self::IMG_SUBDIR;
        @mkdir($directory, 0775, true);
        $filename = uniqid() . '.' . $extBg;
        @copy($request->breadcrumb_image, $directory . $filename);

        return $filename;
    }

    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $lang_id = $lang->id;
        $data['apages'] = Page::where('language_id', $lang_id)->orderBy('id', 'DESC')->get();

        $data['lang_id'] = $lang_id;
        return view('admin.page.index', $data);
    }

    public function settings(Request $request)
    {
        $data['abex'] = BasicExtra::first();

        return view('admin.page.settings', $data);
    }

    public function updateSettings(Request $request)
    {
        $bexs = BasicExtra::all();

        foreach ($bexs as $key => $bex) {
            $bex->custom_page_pagebuilder = $request->custom_page_pagebuilder;
            $bex->save();
        }

        Session::flash('success', "Page settings updated!");
        return back();
    }

    public function create() {
        return view('admin.page.create');
    }

    public function store(Request $request)
    {
        $slug = make_slug($request->name);

        $messages = [
            'language_id.required' => 'The language field is required',
        ];

        $rules = [
            'language_id' => 'required',
            'name' => [
                'required',
                'max:150',
                function ($attribute, $value, $fail) use ($slug) {
                    $pages = Page::all();
                    foreach ($pages as $key => $page) {
                        if (strtolower($slug) == strtolower($page->slug)) {
                            $fail('The title field must be unique.');
                        }
                    }
                }
            ],
            'status' => 'required',
            'serial_number' => 'required|integer',
            'page_type' => ['nullable', 'in:' . implode(',', array_keys(Page::SPECIAL_TYPES))],
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $bex = BasicExtra::firstOrFail();

        $pageType = $request->filled('page_type') ? $request->page_type : null;

        // A special type may exist only once per language: demote the existing one.
        if ($pageType) {
            Page::where('language_id', $request->language_id)
                ->where('page_type', $pageType)
                ->update(['page_type' => null]);
        }

        $page = new Page;
        $page->language_id = $request->language_id;
        $page->name = $request->name;
        $page->title = $request->breadcrumb_title;
        $page->subtitle = $request->breadcrumb_subtitle;
        $page->slug = $slug;
        $page->page_type = $pageType;
        $page->status = $request->status;
        $page->serial_number = $request->serial_number;
        $page->meta_keywords = $request->meta_keywords;
        $page->meta_description = $request->meta_description;

        // Handle breadcrumb image from file manager
        if ($filename = $this->saveBreadcrumbImage($request)) {
            $page->breadcrumb_image = $filename;
        }

        // Handle breadcrumb overlay settings
        $page->breadcrumb_overlay_color = $request->breadcrumb_overlay_color;
        $page->breadcrumb_overlay_opacity = $request->breadcrumb_overlay_opacity;

        if ($bex->custom_page_pagebuilder == 0) {
            $page->body = clean($request->body);
        }
        $page->save();

        Session::flash('success', 'Page created successfully!');
        return "success";
    }

    public function edit($pageID)
    {
        $data['page'] = Page::findOrFail($pageID);
        return view('admin.page.edit', $data);
    }

    public function update(Request $request)
    {
        $slug = make_slug($request->name);
        $pageID = $request->pageid;

        $rules = [
            'name' => [
                'required',
                'max:150',
                function ($attribute, $value, $fail) use ($slug, $pageID) {
                    $pages = Page::all();
                    foreach ($pages as $key => $page) {
                        if ($page->id != $pageID && strtolower($slug) == strtolower($page->slug)) {
                            $fail('The title field must be unique.');
                        }
                    }
                }
            ],
            'status' => 'required',
            'serial_number' => 'required|integer',
            'page_type' => ['nullable', 'in:' . implode(',', array_keys(Page::SPECIAL_TYPES))],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $bex = BasicExtra::firstOrFail();

        $page = Page::findOrFail($pageID);

        $pageType = $request->filled('page_type') ? $request->page_type : null;

        // A special type may exist only once per language: demote the existing one.
        if ($pageType) {
            Page::where('language_id', $page->language_id)
                ->where('page_type', $pageType)
                ->where('id', '!=', $page->id)
                ->update(['page_type' => null]);
        }

        $page->name = $request->name;
        $page->title = $request->breadcrumb_title;
        $page->subtitle = $request->breadcrumb_subtitle;
        $page->slug = $slug;
        $page->page_type = $pageType;
        $page->status = $request->status;
        $page->serial_number = $request->serial_number;
        $page->meta_keywords = $request->meta_keywords;
        $page->meta_description = $request->meta_description;

        // Handle breadcrumb image from file manager
        if ($filename = $this->saveBreadcrumbImage($request)) {
            if ($page->breadcrumb_image) {
                @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $page->breadcrumb_image);
            }
            $page->breadcrumb_image = $filename;
        }

        // Handle breadcrumb overlay settings
        $page->breadcrumb_overlay_color = $request->breadcrumb_overlay_color;
        $page->breadcrumb_overlay_opacity = $request->breadcrumb_overlay_opacity;

        if ($bex->custom_page_pagebuilder == 0) {
            $page->body = clean($request->body);
        }
        $page->save();

        Session::flash('success', 'Page updated successfully!');
        return "success";
    }

    public function delete(Request $request)
    {
        $pageID = $request->pageid;
        $page = Page::findOrFail($pageID);
        // Delete breadcrumb image if exists
        if ($page->breadcrumb_image) {
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $page->breadcrumb_image);
        }
        $page->delete();
        Session::flash('success', 'Page deleted successfully!');
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $page = Page::findOrFail($id);
            // Delete breadcrumb image if exists
            if ($page->breadcrumb_image) {
                @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $page->breadcrumb_image);
            }
            $page->delete();
        }

        Session::flash('success', 'Pages deleted successfully!');
        return "success";
    }

    public function deleteBreadcrumbImage($id)
    {
        $page = Page::findOrFail($id);

        if ($page->breadcrumb_image) {
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $page->breadcrumb_image);
            $page->breadcrumb_image = null;
            $page->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'No image to delete']);
    }

    public function uploadPbImage(Request $request)
    {
        $files = $request->file('files');
        $assets = [];

        foreach ($files as $key => $file) {
            $directory = FRONT_IMG_PATH . "pagebuilder/";
            @mkdir($directory, 0775, true);
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);


            $path = url($directory. $filename);
            $name = $file->getClientOriginalName();

            $assets[] = [
                'name' => $name,
                'type' => 'image',
                'src' =>  $path,
                'height' => 350,
                'width' => 250
            ];
        }

        return response()->json(['data' => $assets]);
    }

    public function removePbImage(Request $request) {
        $path = str_replace(url('/') . '/', '', $request->path);
        @unlink($path);
    }

    public function uploadPbTui(Request $request) {
        $image = $request->base_64;  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = uniqid().'.'.'png';

        $path = FRONT_IMG_PATH . 'pagebuilder/' . $imageName;
        \File::put($path, base64_decode($image));

        $assets[] = [
            'name' => $imageName,
            'type' => 'image',
            'src' =>  url($path),
            'height' => 350,
            'width' => 250
        ];

        return response()->json(['data' => $assets]);
    }
}
