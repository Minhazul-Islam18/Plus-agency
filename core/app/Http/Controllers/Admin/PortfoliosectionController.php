<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BasicSetting as BS;
use App\BasicExtended;
use App\Language;
use Validator;
use Session;

class PortfoliosectionController extends Controller
{
    public function index(Request $request)
    {
        if (empty($request->language)) {
            $data['lang_id'] = 0;
            $data['abs'] = BS::firstOrFail();
            $data['abe'] = BasicExtended::firstOrFail();
        } else {
            $lang = Language::where('code', $request->language)->firstOrFail();
            $data['lang_id'] = $lang->id;
            $data['abs'] = $lang->basic_setting;
            $data['abe'] = $lang->basic_extended;
        }
        return view('admin.home.portfolio-section', $data);
    }

    public function update(Request $request, $langid)
    {
        $portfolioSectionBg = $request->portfolio_section_bg;
        $allowedExts = array('jpg', 'png', 'jpeg', 'svg', 'webp');
        $extPortfolioSectionBg = pathinfo($portfolioSectionBg, PATHINFO_EXTENSION);

        $rules = [
            'portfolio_section_text' => 'required|max:80',
            'portfolio_section_title' => 'required|max:25',
            'portfolio_overlay_color' => 'required',
            'portfolio_overlay_opacity' => 'required|numeric|max:1|min:0'
        ];

        if ($request->filled('portfolio_section_bg')) {
            $rules['portfolio_section_bg'] = [
                function ($attribute, $value, $fail) use ($extPortfolioSectionBg, $allowedExts) {
                    if (!in_array($extPortfolioSectionBg, $allowedExts)) {
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

        $bs = BS::where('language_id', $langid)->firstOrFail();
        $bs->portfolio_section_text = $request->portfolio_section_text;
        $bs->portfolio_section_title = $request->portfolio_section_title;
        $bs->save();

        $be = BasicExtended::where('language_id', $langid)->firstOrFail();

        // Save overlay color and opacity
        $be->portfolio_overlay_color = $request->portfolio_overlay_color;
        $be->portfolio_overlay_opacity = $request->portfolio_overlay_opacity;

        // Handle portfolio section background image upload
        if ($request->filled('portfolio_section_bg')) {
            // Delete old image if exists
            if ($be->portfolio_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->portfolio_section_bg));
            }

            $filename = uniqid() . '.' . $extPortfolioSectionBg;

            // Convert to absolute paths
            // assets folder is one level up from core directory
            $sourcePath = base_path('../' . $portfolioSectionBg);
            $destinationPath = base_path(FRONT_IMG_DIR . $filename);

            // Copy the file
            copy($sourcePath, $destinationPath);

            $be->portfolio_section_bg = $filename;
        }
        // Handle portfolio section background image deletion (only if not uploading new one)
        elseif ($request->delete_portfolio_section_bg == '1') {
            if ($be->portfolio_section_bg) {
                @unlink(base_path(FRONT_IMG_DIR . $be->portfolio_section_bg));
                $be->portfolio_section_bg = null;
            }
        }

        $be->save();

        Session::flash('success', 'Informations updated successfully!');
        return redirect()->back();
    }
}
