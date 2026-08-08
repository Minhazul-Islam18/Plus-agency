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

        return url('/') . "/assets/front/img/summernote/" . $filename;
    }
    public function uploadFileManager(Request $request) {
        $items = $request->items;
        // return $items;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
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
