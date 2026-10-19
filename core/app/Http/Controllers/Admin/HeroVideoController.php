<?php

namespace App\Http\Controllers\Admin;

use App\HeroVideoSetting;
use App\Http\Controllers\Controller;
use App\Language;
use Illuminate\Http\Request;
use Session;
use Validator;

class HeroVideoController extends Controller
{
    private const IMG_SUBDIR = 'hero-video/';

    public function index(Request $request)
    {
        $lang = $request->filled('language')
            ? Language::where('code', $request->language)->firstOrFail()
            : Language::where('is_default', 1)->firstOrFail();

        $data['lang_id'] = $lang->id;
        $data['video'] = HeroVideoSetting::firstOrNew(['language_id' => $lang->id]);
        $data['video']->setRelation('language', $lang);

        return view('admin.home.hero.video-dark', $data);
    }

    public function update(Request $request, $langid)
    {
        Language::findOrFail($langid);

        $allowedVideoExts = ['mp4'];
        $allowedImageExts = allowed_image_extensions();

        $video = $request->video_file;
        $extVideo = pathinfo($video, PATHINFO_EXTENSION);
        $poster = $request->poster_image;
        $extPoster = pathinfo($poster, PATHINFO_EXTENSION);

        $rules = [
            'eyebrow' => 'nullable',
            'title' => 'nullable',
            'title_accent' => 'nullable',
            'rotating_titles' => 'nullable',
            'rotating_titles_accent' => 'nullable',
            'text' => 'nullable',
            'button_text' => 'nullable',
            'button_url' => 'nullable',
            'overlay_color' => 'required',
            'overlay_opacity' => 'required|numeric|max:1|min:0',
            'disable_on_mobile' => 'nullable',
        ];

        if ($request->filled('video_file')) {
            $rules['video_file'] = [
                function ($attribute, $value, $fail) use ($extVideo, $allowedVideoExts) {
                    if (!in_array(strtolower($extVideo), $allowedVideoExts)) {
                        return $fail('Only MP4 video is allowed.');
                    }
                }
            ];
        }

        if ($request->filled('poster_image')) {
            $rules['poster_image'] = [
                function ($attribute, $value, $fail) use ($extPoster, $allowedImageExts) {
                    if (!in_array(strtolower($extPoster), $allowedImageExts)) {
                        return $fail('Only ' . allowed_image_extensions_label() . ' image is allowed.');
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $setting = HeroVideoSetting::firstOrNew(['language_id' => $langid]);
        $setting->eyebrow = $request->eyebrow;
        $setting->title = $request->title;
        $setting->title_accent = $request->title_accent;
        $setting->rotating_titles = $request->rotating_titles;
        $setting->rotating_titles_accent = $request->rotating_titles_accent;
        $setting->text = $request->text;
        $setting->button_text = $request->button_text;
        $setting->button_url = $request->button_url;
        $setting->overlay_color = $request->overlay_color;
        $setting->overlay_opacity = $request->overlay_opacity;
        $setting->disable_on_mobile = $request->boolean('disable_on_mobile');

        if ($request->filled('video_file')) {
            if ($setting->video_file) {
                @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $setting->video_file);
            }
            $filename = uniqid() . '.' . $extVideo;
            @mkdir(FRONT_IMG_PATH . self::IMG_SUBDIR, 0775, true);
            @copy($video, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $setting->video_file = $filename;
        }

        if ($request->filled('poster_image')) {
            if ($setting->poster_image) {
                @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $setting->poster_image);
            }
            $filename = uniqid() . '.' . $extPoster;
            @mkdir(FRONT_IMG_PATH . self::IMG_SUBDIR, 0775, true);
            @copy($poster, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $setting->poster_image = $filename;
        }

        $setting->save();

        // Mirrors DarkHeroController::update() exactly: the view's form
        // uses id="ajaxForm", which custom.js's global AJAX interceptor
        // catches — a bare "success" string here is what that JS expects,
        // not a real page navigation (unlike SliderController::updateOverlay(),
        // whose form deliberately isn't #ajaxForm and needs a real redirect).
        Session::flash('success', 'Dark theme hero video updated successfully!');
        return 'success';
    }
}
