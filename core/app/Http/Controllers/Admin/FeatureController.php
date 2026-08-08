<?php

namespace App\Http\Controllers\Admin;

use Validator;
use App\Feature;
use App\Language;
use App\BasicExtended;
use App\BasicSetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

class FeatureController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();
        $lang_id = $lang->id;
        $data['features'] = Feature::where('language_id', $lang_id)->orderBy('id', 'DESC')->get();
        $data['lang_id'] = $lang_id;
        $data['abs'] = BasicSetting::where('language_id', $lang_id)->first();

        return view('admin.home.feature.index', $data);
    }

    public function updateSection(Request $request, $langid)
    {
        $rules = [
            'feature_section_title' => 'nullable',
            'feature_section_subtitle' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->feature_section_title = $request->feature_section_title;
        $bs->feature_section_subtitle = $request->feature_section_subtitle;
        $bs->save();

        Session::flash('success', 'Features section heading updated successfully!');
        return 'success';
    }

    public function edit($id)
    {
        $data['feature'] = Feature::findOrFail($id);
        return view('admin.home.feature.edit', $data);
    }

    public function store(Request $request)
    {
        $count = Feature::where('language_id', $request->language_id)->count();
        if ($count == 4) {
            Session::flash('warning', 'You cannot add more than 4 features!');
            return "success";
        }

        $messages = [
            'language_id.required' => 'The language field is required'
        ];

        $rules = [
            'language_id' => 'required',
            'icon' => 'required',
            'title' => 'required|max:80',
            'color' => 'required',
            'serial_number' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $feature = new Feature;
        $feature->icon = $request->icon;
        $feature->language_id = $request->language_id;
        $feature->title = $request->title;
        $feature->color = $request->color;
        $feature->serial_number = $request->serial_number;
        $feature->save();

        Session::flash('success', 'Feature added successfully!');
        return "success";
    }

    public function update(Request $request)
    {
        $rules = [
            'icon' => 'required',
            'title' => 'required|max:80',
            'color' => 'required',
            'serial_number' => 'required|integer',
        ];

        $request->validate($rules);

        $feature = Feature::findOrFail($request->feature_id);
        $feature->icon = $request->icon;
        $feature->title = $request->title;
        $feature->color = $request->color;
        $feature->serial_number = $request->serial_number;
        $feature->save();

        Session::flash('success', 'Feature updated successfully!');
        return back();
    }

    public function delete(Request $request)
    {

        $feature = Feature::findOrFail($request->feature_id);
        $feature->delete();

        Session::flash('success', 'Feature deleted successfully!');
        return back();
    }

    public function status(Request $request)
    {
        $feature = Feature::findOrFail($request->id);
        $feature->status = $request->status;
        $feature->save();

        return response()->json(['success' => true]);
    }
}
