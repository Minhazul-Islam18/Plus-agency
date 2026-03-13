<?php

namespace App\Http\Controllers\Admin;

use App\TenderCategory;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class TenderCategoryController extends Controller
{
  public function index(Request $request)
  {
    $language = Language::where('code', $request->language)->first();
    $language_id = $language->id;

    $tender_categories = TenderCategory::where('language_id', $language_id)
      ->orderBy('serial_number', 'asc')
      ->paginate(10);

    return view('admin.tender.tender_category.index', compact('tender_categories'));
  }

  public function store(Request $request)
  {
    $rules = [
      'language_id'   => 'required',
      'name'          => 'required',
      'status'        => 'required',
      'serial_number' => 'required|integer'
    ];

    $rules_msg = [
      'language_id.required' => 'The language field is required'
    ];

    $validator = Validator::make($request->all(), $rules, $rules_msg);

    if ($validator->fails()) {
      $validator->getMessageBag()->add('error', 'true');
      return response()->json($validator->errors());
    }

    $tender_category = new TenderCategory;
    $tender_category->language_id   = $request->language_id;
    $tender_category->name          = $request->name;
    $tender_category->status        = $request->status;
    $tender_category->serial_number = $request->serial_number;
    $tender_category->save();

    Session::flash('success', 'New Tender Category Has Been Added');

    return 'success';
  }

  public function update(Request $request)
  {
    $rules = [
      'name'          => 'required|max:255',
      'status'        => 'required',
      'serial_number' => 'required|integer'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      $validator->getMessageBag()->add('error', 'true');
      return response()->json($validator->errors());
    }

    $tender_category = TenderCategory::findOrFail($request->tender_category_id);
    $tender_category->name          = $request->name;
    $tender_category->status        = $request->status;
    $tender_category->serial_number = $request->serial_number;
    $tender_category->save();

    Session::flash('success', 'Tender Category Has Been Updated Successfully');

    return 'success';
  }

  public function delete(Request $request)
  {
    $tender_category = TenderCategory::findOrFail($request->tender_category_id);

    if ($tender_category->tenders->count() > 0) {
      Session::flash('warning', 'First Delete All The Tenders of This Category');

      return back();
    }

    $tender_category->delete();

    Session::flash('success', 'Tender Category Has Been Deleted Successfully');

    return back();
  }

  public function bulkDelete(Request $request)
  {
    $ids = $request->ids;

    foreach ($ids as $id) {
      $tender_category = TenderCategory::findOrFail($id);

      if ($tender_category->tenders->count() > 0) {
        Session::flash('warning', 'First Delete All The Tenders of Those Categories');

        return 'success';
      }
    }

    foreach ($ids as $id) {
      TenderCategory::findOrFail($id)->delete();
    }

    Session::flash('success', 'Tender Categories Have Been Deleted');

    return 'success';
  }
}
