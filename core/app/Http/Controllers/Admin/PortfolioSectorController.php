<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Language;
use App\PortfolioSector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Manageable per-language "Secteur" list (Énergie, Eau potable, Génie
 * civil, ...) for the Portfolio module — distinct from the existing
 * Service model (the firm's own service offerings). See
 * ~/Documents/15-Portoflios/Recommandation sur Portofolios-*.docx.
 * Mirrors GalleryCategoryController's CRUD shape.
 */
class PortfolioSectorController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->first();

        $sectors = PortfolioSector::where('language_id', $language->id)
            ->orderBy('serial_number', 'asc')
            ->paginate(10);

        return view('admin.portfolio.sectors', compact('sectors'));
    }

    public function store(Request $request)
    {
        $rules = [
            'language_id' => 'required',
            'name' => 'required|max:255',
            'status' => 'required',
            'serial_number' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        PortfolioSector::create($request->all());

        Session::flash('success', 'New sector added successfully.');
        return 'success';
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'status' => 'required',
            'serial_number' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        PortfolioSector::findOrFail($request->sectorId)->update($request->all());

        Session::flash('success', 'Sector updated successfully.');
        return 'success';
    }

    public function delete(Request $request)
    {
        $sector = PortfolioSector::findOrFail($request->sectorId);

        if ($sector->portfolios()->count() > 0) {
            Session::flash('warning', 'First reassign or delete all portfolios using this sector!');
            return redirect()->back();
        }

        $sector->delete();

        Session::flash('success', 'Sector deleted successfully.');
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $sector = PortfolioSector::findOrFail($id);
            if ($sector->portfolios()->count() > 0) {
                Session::flash('warning', 'First reassign or delete all portfolios using the selected sectors!');
                return 'success';
            }
        }

        foreach ($ids as $id) {
            PortfolioSector::findOrFail($id)->delete();
        }

        Session::flash('success', 'Sectors deleted successfully.');
        return 'success';
    }

    /** Cascading Language -> Sectors AJAX lookup, mirrors PortfolioController::getservices(). */
    public function getSectors($langid)
    {
        return PortfolioSector::where('language_id', $langid)->where('status', 1)->orderBy('serial_number', 'asc')->get();
    }
}
