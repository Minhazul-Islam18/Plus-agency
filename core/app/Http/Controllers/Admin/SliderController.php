<?php

namespace App\Http\Controllers\Admin;

use App\BasicExtended;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Slider;
use App\Language;
use Validator;
use Session;

class SliderController extends Controller
{
    private const IMG_SUBDIR = 'sliders/';
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->first();

        $lang_id = $lang->id;
        $data['sliders'] = Slider::where('language_id', $lang_id)->orderBy('id', 'DESC')->get();

        $data['lang_id'] = $lang_id;
        return view('admin.home.hero.slider.index', $data);
    }

    public function edit($id)
    {
        $data['slider'] = Slider::findOrFail($id);
        $data['isDarkTheme'] = $this->isDarkTheme($data['slider']->language_id);
        return view('admin.home.hero.slider.edit', $data);
    }

    /**
     * Title/text/button font-size fields only apply to the light theme's
     * inline-styled slider markup — the dark theme's hero slider uses its
     * own fixed clamp() typography and never reads these fields at all.
     */
    private function isDarkTheme($languageId): bool
    {
        $lang = Language::find($languageId);
        return (bool) ($lang && $lang->basic_extended && $lang->basic_extended->theme_version == 'dark');
    }

    public function store(Request $request)
    {
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $messages = [
            'language_id.required' => 'The language field is required'
        ];

        $fontSizeRule = $this->isDarkTheme($request->language_id) ? 'nullable|integer|digits_between:1,3' : 'required|integer|digits_between:1,3';

        $rules = [
            'language_id' => 'required',
            'image' => 'required',
            'title' => 'nullable',
            'title_font_size' => $fontSizeRule,
            'text' => 'nullable',
            'text_font_size' => $fontSizeRule,
            'button_text' => 'nullable',
            'button_text_font_size' => $fontSizeRule,
            'button_url' => 'nullable|max:255',
            'serial_number' => 'required|integer',
        ];

        if ($request->filled('image')) {
            $rules['image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $slider = new Slider;
        $slider->language_id = $request->language_id;
        $slider->title = $request->title;
        $slider->title_font_size = $request->title_font_size;
        $slider->text = $request->text;
        $slider->text_font_size = $request->text_font_size;

        $slider->button_text = $request->button_text;
        $slider->button_text_font_size = $request->button_text_font_size;
        $slider->button_url = $request->button_url;

        if ($request->filled('image')) {
            $filename = uniqid() .'.'. $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $slider->image = $filename;
        }

        $slider->serial_number = $request->serial_number;
        $slider->save();

        Session::flash('success', 'Slider added successfully!');
        return "success";
    }

    public function update(Request $request)
    {
        $image = $request->image;
        $allowedExts = allowed_image_extensions();
        $extImage = pathinfo($image, PATHINFO_EXTENSION);

        $slider = Slider::findOrFail($request->slider_id);
        $fontSizeRule = $this->isDarkTheme($slider->language_id) ? 'nullable|integer|digits_between:1,3' : 'required|integer|digits_between:1,3';

        $rules = [
            'title' => 'nullable',
            'title_font_size' => $fontSizeRule,
            'text' => 'nullable',
            'text_font_size' => $fontSizeRule,
            'button_text' => 'nullable',
            'button_text_font_size' => $fontSizeRule,
            'button_url' => 'nullable|max:255',
            'serial_number' => 'required|integer',
        ];

        if ($request->filled('image')) {
            $rules['image'] = [
                function ($attribute, $value, $fail) use ($extImage, $allowedExts) {
                    if (!in_array($extImage, $allowedExts)) {
                        return $fail("Only png, jpg, jpeg, svg image is allowed");
                    }
                }
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errmsgs = $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $slider->title = $request->title;
        $slider->title_font_size = $request->title_font_size;

        if ($request->filled('image')) {
            @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $slider->image);
            $filename = uniqid() .'.'. $extImage;
            @copy($image, FRONT_IMG_PATH . self::IMG_SUBDIR . $filename);
            $slider->image = $filename;
        }

        $slider->text = $request->text;
        $slider->text_font_size = $request->text_font_size;

        $slider->button_text = $request->button_text;
        $slider->button_text_font_size = $request->button_text_font_size;
        $slider->button_url = $request->button_url;
        $slider->serial_number = $request->serial_number;
        $slider->save();

        Session::flash('success', 'Slider updated successfully!');
        return "success";
    }

    public function delete(Request $request)
    {

        $slider = Slider::findOrFail($request->slider_id);
        @unlink(FRONT_IMG_PATH . self::IMG_SUBDIR . $slider->image);
        $slider->delete();

        Session::flash('success', 'Slider deleted successfully!');
        return back();
    }
}
