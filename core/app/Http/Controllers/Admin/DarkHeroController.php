<?php

namespace App\Http\Controllers\Admin;

use App\DarkHeroSetting;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Http\Request;
use Session;

class DarkHeroController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->filled('language')
            ? Language::where('code', $request->language)->firstOrFail()
            : Language::where('is_default', 1)->firstOrFail();

        $data['lang_id'] = $lang->id;
        $data['abs'] = DarkHeroSetting::firstOrNew(['language_id' => $lang->id]);
        $data['abs']->setRelation('language', $lang);

        return view('admin.home.hero.dark', $data);
    }

    public function update(Request $request, $langid)
    {
        Language::findOrFail($langid);

        $rules = [
            'eyebrow' => 'nullable',
            'title' => 'nullable',
            'rotating_titles' => 'nullable',
            'text' => 'nullable',
            'button_text' => 'nullable',
            'button_url' => 'nullable',
            'meta_left' => 'nullable',
            'meta_right' => 'nullable',
        ];

        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $setting = DarkHeroSetting::firstOrNew(['language_id' => $langid]);
        $setting->eyebrow = $request->eyebrow;
        $setting->title = $request->title;
        $setting->rotating_titles = $request->rotating_titles;
        $setting->text = $request->text;
        $setting->button_text = $request->button_text;
        $setting->button_url = $request->button_url;
        $setting->meta_left = $request->meta_left;
        $setting->meta_right = $request->meta_right;
        $setting->save();

        Session::flash('success', 'Dark theme hero updated successfully!');
        return 'success';
    }
}
