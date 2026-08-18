<?php

namespace App\Http\Controllers\Admin;

use App\BasicSetting;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogSettingsController extends Controller
{
    public function settings(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['bsData'] = BasicSetting::where('language_id', $lang->id)->first();

        return view('admin.blog.settings', $data);
    }

    public function updateSettings(Request $request, $langid)
    {
        $request->validate([
            'blog_breadcrumb_overlay_color' => 'nullable|max:20',
            'blog_breadcrumb_overlay_opacity' => 'nullable|numeric|min:0|max:1',
        ]);

        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();
        $bs->blog_breadcrumb_overlay_color = $request->blog_breadcrumb_overlay_color;
        $bs->blog_breadcrumb_overlay_opacity = $request->blog_breadcrumb_overlay_opacity;

        if ($request->filled('blog_breadcrumb_bg')) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'avif'];
            $extBg = pathinfo($request->blog_breadcrumb_bg, PATHINFO_EXTENSION);
            if (in_array($extBg, $allowedExts)) {
                @unlink(FRONT_IMG_PATH . $bs->blog_breadcrumb_bg);
                $filename = uniqid() . '.' . $extBg;
                @copy($request->blog_breadcrumb_bg, FRONT_IMG_PATH . $filename);
                $bs->blog_breadcrumb_bg = $filename;
            }
        }

        $bs->save();

        $lang = Language::find($langid);

        Session::flash('success', 'Settings updated successfully.');

        return redirect()->route('admin.blog.settings', ['language' => $lang->code]);
    }

    public function deleteBreadcrumbBg($langid)
    {
        $bs = BasicSetting::where('language_id', $langid)->firstOrFail();

        if ($bs && $bs->blog_breadcrumb_bg) {
            @unlink(FRONT_IMG_PATH . $bs->blog_breadcrumb_bg);
            $bs->blog_breadcrumb_bg = null;
            $bs->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
