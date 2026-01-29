<?php

namespace App\Http\Controllers\Admin;

use App\BasicSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogSettingsController extends Controller
{
  public function settings()
  {
    $data['bs'] = BasicSetting::first();

    return view('admin.blog.settings', $data);
  }

  public function updateSettings(Request $request)
  {
    $request->validate([
      'blog_breadcrumb_overlay_color' => 'nullable|max:20',
      'blog_breadcrumb_overlay_opacity' => 'nullable|numeric|min:0|max:1',
    ]);

    $bs = BasicSetting::first();
    $bs->blog_breadcrumb_overlay_color = $request->blog_breadcrumb_overlay_color;
    $bs->blog_breadcrumb_overlay_opacity = $request->blog_breadcrumb_overlay_opacity;

    if ($request->filled('blog_breadcrumb_bg')) {
      $allowedExts = ['jpg', 'jpeg', 'png'];
      $extBg = pathinfo($request->blog_breadcrumb_bg, PATHINFO_EXTENSION);
      if (in_array($extBg, $allowedExts)) {
        @unlink('assets/front/img/' . $bs->blog_breadcrumb_bg);
        $filename = uniqid() . '.' . $extBg;
        @copy($request->blog_breadcrumb_bg, 'assets/front/img/' . $filename);
        $bs->blog_breadcrumb_bg = $filename;
      }
    }

    $bs->save();

    Session::flash('success', 'Settings updated successfully.');

    return redirect()->back();
  }

  public function deleteBreadcrumbBg()
  {
    $bs = BasicSetting::first();

    if ($bs && $bs->blog_breadcrumb_bg) {
      @unlink('assets/front/img/' . $bs->blog_breadcrumb_bg);
      $bs->blog_breadcrumb_bg = null;
      $bs->save();

      return response()->json(['success' => true]);
    }

    return response()->json(['success' => false], 404);
  }
}
