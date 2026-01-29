<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtra;
use App\BasicSetting;
use App\GalleryCategory;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class GalleryCategoryController extends Controller
{
  public function settings()
  {
    $data['abex'] = BasicExtra::first();
    $data['bs'] = BasicSetting::first();

    return view('admin.gallery.settings', $data);
  }

  public function updateSettings(Request $request)
  {
    $request->validate([
      'gallery_breadcrumb_overlay_color' => 'nullable|max:20',
      'gallery_breadcrumb_overlay_opacity' => 'nullable|numeric|min:0|max:1',
    ]);

    // Update BasicExtra for gallery_category_status
    $bexs = BasicExtra::all();
    foreach ($bexs as $bex) {
      $bex->update([
        'gallery_category_status' => $request->gallery_category_status
      ]);
    }

    // Update BasicSetting for breadcrumb fields
    $bs = BasicSetting::first();
    $bs->gallery_breadcrumb_overlay_color = $request->gallery_breadcrumb_overlay_color;
    $bs->gallery_breadcrumb_overlay_opacity = $request->gallery_breadcrumb_overlay_opacity;

    if ($request->filled('gallery_breadcrumb_bg')) {
      $allowedExts = ['jpg', 'jpeg', 'png'];
      $extBg = pathinfo($request->gallery_breadcrumb_bg, PATHINFO_EXTENSION);
      if (in_array($extBg, $allowedExts)) {
        @unlink('assets/front/img/' . $bs->gallery_breadcrumb_bg);
        $filename = uniqid() . '.' . $extBg;
        @copy($request->gallery_breadcrumb_bg, 'assets/front/img/' . $filename);
        $bs->gallery_breadcrumb_bg = $filename;
      }
    }

    $bs->save();

    Session::flash('success', 'Settings updated successfully.');

    return redirect()->back();
  }

  public function deleteBreadcrumbBg()
  {
    $bs = BasicSetting::first();

    if ($bs && $bs->gallery_breadcrumb_bg) {
      @unlink('assets/front/img/' . $bs->gallery_breadcrumb_bg);
      $bs->gallery_breadcrumb_bg = null;
      $bs->save();

      return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 404);
  }


  public function index(Request $request)
  {
    $language = Language::where('code', $request->language)->first();

    $categories = GalleryCategory::where('language_id', $language->id)
      ->orderBy('id', 'desc')
      ->paginate(10);

    return view('admin.gallery.categories', compact('categories'));
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

    GalleryCategory::create($request->all());

    Session::flash('success', 'New gallery category added successfully.');

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

    GalleryCategory::findOrFail($request->categoryId)->update($request->all());

    Session::flash('success', 'Gallery category updated successfully.');

    return 'success';
  }

  public function delete(Request $request)
  {
    $category = GalleryCategory::findOrFail($request->categoryId);

    if ($category->galleryImg->count() > 0) {
      Session::flash('warning', 'First delete all the images of this category');

      return redirect()->back();
    }

    $category->delete();

    Session::flash('success', 'Gallery category deleted successfully.');

    return redirect()->back();
  }

  public function bulkDelete(Request $request)
  {
    $ids = $request->ids;

    foreach ($ids as $id) {
      $category = GalleryCategory::findOrFail($id);

      if ($category->galleryImg->count() > 0) {
        Session::flash('warning', 'First delete all the images of those categories');

        return 'success';
      }
    }

    foreach ($ids as $id) {
      $category = GalleryCategory::findOrFail($id);

      $category->delete();
    }

    Session::flash('success', 'Gallery categories deleted successfully.');

    return 'success';
  }
}
