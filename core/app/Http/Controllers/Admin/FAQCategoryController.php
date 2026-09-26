<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtra;
use App\BasicSetting;
use App\FAQCategory;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class FAQCategoryController extends Controller
{
    public function settings(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abex'] = BasicExtra::where('language_id', $lang->id)->first();
        $data['bsData'] = BasicSetting::where('language_id', $lang->id)->first();

        return view('admin.home.faq.settings', $data);
    }

    public function updateSettings(Request $request, $langid)
    {
        $request->validate([
            'faq_breadcrumb_overlay_color' => 'nullable|max:20',
            'faq_breadcrumb_overlay_opacity' => 'nullable|numeric|min:0|max:1',
            'faq_intro_text' => 'nullable|string|max:1000',
            'faq_contact_email' => 'nullable|email|max:255',
            'faq_whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]{6,30}$/'],
            'faq_frequent_max' => 'required|integer|min:1|max:20',
        ], [
            'faq_whatsapp.regex' => 'Enter a valid WhatsApp number (digits, with optional + country code).',
        ]);

        // Update BasicExtra for faq_category_status
        $bexs = BasicExtra::all();
        foreach ($bexs as $bex) {
            $bex->update([
                'faq_category_status' => $request->faq_category_status
            ]);
        }

        // Update BasicSetting for breadcrumb fields
        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->faq_breadcrumb_overlay_color = $request->faq_breadcrumb_overlay_color;
        $bs->faq_breadcrumb_overlay_opacity = $request->faq_breadcrumb_overlay_opacity;
        $bs->faq_intro_text = $request->faq_intro_text;
        $bs->faq_contact_email = $request->faq_contact_email;
        $bs->faq_whatsapp = $request->faq_whatsapp;
        $bs->faq_frequent_max = (int) $request->faq_frequent_max;

        if ($request->filled('faq_hero_image')) {
            $allowedExts = allowed_image_extensions();
            $extHero = pathinfo($request->faq_hero_image, PATHINFO_EXTENSION);
            if (in_array($extHero, $allowedExts)) {
                if ($bs->faq_hero_image) {
                    @unlink(FRONT_IMG_PATH . $bs->faq_hero_image);
                }
                $filename = uniqid() . '.' . $extHero;
                @copy($request->faq_hero_image, FRONT_IMG_PATH . $filename);
                $bs->faq_hero_image = $filename;
            }
        }

        if ($request->filled('faq_breadcrumb_bg')) {
            $allowedExts = allowed_image_extensions();
            $extBg = pathinfo($request->faq_breadcrumb_bg, PATHINFO_EXTENSION);
            if (in_array($extBg, $allowedExts)) {
                @unlink(FRONT_IMG_PATH . $bs->faq_breadcrumb_bg);
                $filename = uniqid() . '.' . $extBg;
                @copy($request->faq_breadcrumb_bg, FRONT_IMG_PATH . $filename);
                $bs->faq_breadcrumb_bg = $filename;
            }
        }

        $bs->save();

        // Lowering the max takes effect immediately: the most-viewed panel
        // drops its oldest entries down to the new size (no-op if it fits).
        \App\Services\FaqViewService::trimToMax((int) $langid);

        $lang = Language::find($langid);

        Session::flash('success', 'Settings updated successfully.');

        return redirect()->route('admin.faq.settings', ['language' => $lang->code]);
    }

    public function deleteBreadcrumbBg($langid)
    {
        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();

        if ($bs && $bs->faq_breadcrumb_bg) {
            @unlink(FRONT_IMG_PATH . $bs->faq_breadcrumb_bg);
            $bs->faq_breadcrumb_bg = null;
            $bs->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }


    public function deleteHeroImage($langid)
    {
        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();

        if ($bs->faq_hero_image) {
            @unlink(FRONT_IMG_PATH . $bs->faq_hero_image);
            $bs->faq_hero_image = null;
            $bs->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->first();

        $categories = FAQCategory::where('language_id', $language->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.home.faq.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $rules = [
            'language_id' => 'required',
            'name' => 'required',
            'status' => 'required',
            'serial_number' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');

            return response()->json($validator->errors());
        }

        FAQCategory::create($request->all());

        Session::flash('success', 'New faq category added successfully.');

        return 'success';
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required',
            'status' => 'required',
            'serial_number' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');

            return response()->json($validator->errors());
        }

        FAQCategory::findOrFail($request->categoryId)->update($request->all());

        Session::flash('success', 'FAQ category updated successfully.');

        return 'success';
    }

    public function delete(Request $request)
    {
        $category = FAQCategory::findOrFail($request->categoryId);

        if ($category->frequentlyAskedQuestion->count() > 0) {
            Session::flash('warning', 'First delete all the faqs of this category');

            return redirect()->back();
        }

        $category->delete();

        Session::flash('success', 'FAQ category deleted successfully.');

        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $category = FAQCategory::findOrFail($id);

            if ($category->frequentlyAskedQuestion->count() > 0) {
                Session::flash('warning', 'First delete all the faqs of those categories');

                return 'success';
            }
        }

        foreach ($ids as $id) {
            $category = FAQCategory::findOrFail($id);

            $category->delete();
        }

        Session::flash('success', 'FAQ categories deleted successfully.');

        return 'success';
    }
}
