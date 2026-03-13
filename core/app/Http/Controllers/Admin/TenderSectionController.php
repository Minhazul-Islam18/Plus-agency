<?php

namespace App\Http\Controllers\Admin;

use App\TenderModule;
use App\TenderSection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TenderSectionController extends Controller
{
    public function index($id)
    {
        $module   = TenderModule::findOrFail($id);
        $sections = TenderSection::where('tender_module_id', $module->id)->get();

        return view('admin.tender.section.index', compact('module', 'sections'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $section                  = new TenderSection;
        $section->tender_module_id = $request->module_id;
        $section->name            = $request->name;
        $section->save();

        Session::flash('success', 'Section Added Successfully');

        return 'success';
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $section       = TenderSection::findOrFail($request->section_id);
        $section->name = $request->name;
        $section->save();

        Session::flash('success', 'Section Updated Successfully');

        return 'success';
    }

    public function delete(Request $request)
    {
        $section = TenderSection::findOrFail($request->section_id);
        $section->delete();

        Session::flash('success', 'Section Deleted Successfully');

        return back();
    }

    public function bulkDelete(Request $request)
    {
        foreach ($request->ids as $id) {
            TenderSection::findOrFail($id)->delete();
        }

        Session::flash('success', 'Sections Deleted Successfully');

        return 'success';
    }
}
