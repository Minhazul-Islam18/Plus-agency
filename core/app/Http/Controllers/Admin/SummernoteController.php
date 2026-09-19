<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SummernoteController extends Controller
{
    private const IMG_SUBDIR = 'summernote/';
    public function upload(Request $request) {
        $img = $request->file('image');
        $filename = uniqid() . '.' . $img->getClientOriginalExtension();
        $img->move(FRONT_IMG_PATH . self::IMG_SUBDIR, $filename);

        $url = url('/') . "/assets/front/img/summernote/" . $filename;
        // A bare string return gets wrapped as text/html, which Laravel
        // Debugbar (enabled on both dev and prod here) appends its own
        // widget markup to — summernote's insertImage() then set that
        // whole contaminated blob as the <img src>, so nothing ever
        // rendered. Explicit text/plain makes Debugbar skip injecting.
        return response($url)->header('Content-Type', 'text/plain');
    }
    public function uploadFileManager(Request $request) {
        $items = $request->items;
        // return $items;
        $allowedExts = allowed_image_extensions();
        foreach ($items as $key => $item) {
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowedExts)) {
                return response()->json(['status' => 'error', 'message' => "Only png, jpg, jpeg, svg images are allowed"]);
            }
        }

        $urls = [];
        foreach ($items as $key => $item) {
            $ext = pathinfo($item, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            @copy($item, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $urls[] = url(FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
        }

        return response()->json(['status' => 'success', 'urls' => $urls]);
    }
}
