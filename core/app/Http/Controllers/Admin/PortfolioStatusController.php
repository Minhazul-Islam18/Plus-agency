<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Language;
use App\PortfolioStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Manageable per-language "Statut" list (Ongoing/Pending/Completed, or
 * whatever the admin wants) — replaces the old hardcoded 3-value `status`
 * string enum on Portfolio. Mirrors PortfolioSectorController exactly
 * (admin request: "Like Sector, Add Status module").
 */
class PortfolioStatusController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->first();

        $statuses = PortfolioStatus::where('language_id', $language->id)
            ->orderBy('serial_number', 'asc')
            ->paginate(10);

        return view('admin.portfolio.statuses', compact('statuses'));
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

        PortfolioStatus::create($request->all());

        Session::flash('success', 'New status added successfully.');
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

        PortfolioStatus::findOrFail($request->statusId)->update($request->all());

        Session::flash('success', 'Status updated successfully.');
        return 'success';
    }

    public function delete(Request $request)
    {
        $status = PortfolioStatus::findOrFail($request->statusId);

        if ($status->portfolios()->count() > 0) {
            Session::flash('warning', 'First reassign or delete all portfolios using this status!');
            return redirect()->back();
        }

        $status->delete();

        Session::flash('success', 'Status deleted successfully.');
        return redirect()->back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $status = PortfolioStatus::findOrFail($id);
            if ($status->portfolios()->count() > 0) {
                Session::flash('warning', 'First reassign or delete all portfolios using the selected statuses!');
                return 'success';
            }
        }

        foreach ($ids as $id) {
            PortfolioStatus::findOrFail($id)->delete();
        }

        Session::flash('success', 'Statuses deleted successfully.');
        return 'success';
    }

    /** Cascading Language -> Statuses AJAX lookup, mirrors PortfolioSectorController::getSectors(). */
    public function getStatuses($langid)
    {
        return PortfolioStatus::where('language_id', $langid)->where('status', 1)->orderBy('serial_number', 'asc')->get();
    }
}
