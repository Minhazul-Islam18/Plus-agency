<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Language;
use App\Portfolio;
use App\PortfolioSector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

/**
 * Manageable per-language "Secteur" list (Énergie, Eau potable, Génie
 * civil, ...) for the Portfolio module — distinct from the existing
 * Service model (the firm's own service offerings). See
 * ~/Documents/15-Portoflios/Recommandation sur Portofolios-*.docx.
 *
 * Extended into a 2-level Sector -> Subsector taxonomy (master-detail UI —
 * left panel lists top-level sectors, right panel manages the selected
 * one's subsectors). Subsectors are just PortfolioSector rows with
 * parent_id set — same table/model, see PortfolioSector::subsectors().
 */
class PortfolioSectorController extends Controller
{
    public function index(Request $request)
    {
        // Falls back to the site's default language when ?language= is
        // missing or doesn't match anything (a stale bookmark, hitting the
        // bare URL directly, or the requested language having since been
        // removed) — same as landing on this page via the sidebar link
        // does, instead of a hard 500. Redirected (not just silently
        // substituted) so the URL/language dropdown end up consistent.
        $language = Language::where('code', $request->language)->first();
        if (!$language) {
            $fallback = Language::where('is_default', 1)->first() ?? Language::first();
            if ($fallback) {
                return redirect()->route('admin.portfolio.sector.index', ['language' => $fallback->code]);
            }
            abort(404, 'No languages configured.');
        }

        $sectors = PortfolioSector::topLevel()
            ->where('language_id', $language->id)
            ->withCount('subsectors')
            ->orderBy('serial_number', 'asc')
            ->get();

        // Whichever sector the left panel opens on — the one requested via
        // ?sector=, else just the first. Rendered server-side so the page
        // isn't blank on first load; switching sectors afterward is AJAX
        // (see subsectorPanel()) instead of a full reload.
        $activeSector = null;
        if ($request->filled('sector')) {
            $activeSector = $sectors->firstWhere('id', (int) $request->sector);
        }
        if (!$activeSector) {
            $activeSector = $sectors->first();
        }

        $panelHtml = $activeSector ? $this->renderSubsectorPanel($activeSector, $request) : null;

        return view('admin.portfolio.sectors', compact('sectors', 'activeSector', 'panelHtml'));
    }

    /** AJAX: right panel's content for a different sector / a search-or-filter change / a page change. */
    public function subsectorPanel(Request $request, $sectorId)
    {
        $sector = PortfolioSector::topLevel()->findOrFail($sectorId);

        return $this->renderSubsectorPanel($sector, $request);
    }

    private function renderSubsectorPanel(PortfolioSector $sector, Request $request): string
    {
        $query = $sector->subsectors();

        if ($request->filled('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->search) . '%']);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', (int) $request->status);
        }

        // subsectors() already orders by serial_number — no need to repeat it.
        $subsectors = $query->paginate(10)->appends($request->query());

        return view('admin.portfolio.partials.subsector_panel', [
            'sector' => $sector,
            'subsectors' => $subsectors,
        ])->render();
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

        // Always a top-level sector — subsectors are only ever created
        // through storeSubsector() below, scoped to a parent.
        PortfolioSector::create($request->only(['language_id', 'name', 'icon', 'status', 'serial_number']));

        Session::flash('success', 'New sector added successfully.');
        return 'success';
    }

    public function update(Request $request)
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

        $sector = PortfolioSector::findOrFail($request->sectorId);

        // Sector's language is now editable (was fixed at create time before,
        // with no way to fix a mistake). Sector is a language-scoped list —
        // moving it to a different language would silently leave any
        // portfolio still pointing at it referencing a sector under the
        // WRONG language, so those references are cleared instead (matches
        // the same guard delete()/bulkDelete() above already apply, just
        // clearing the reference here instead of blocking the save). A
        // sector's OWN subsectors move right along with it (same parent_id,
        // untouched) — but portfolios tagged at THOSE subsectors are
        // exactly as stale as ones tagged at the sector itself, so both get
        // cleared.
        if ((int) $request->language_id !== (int) $sector->language_id) {
            Portfolio::where('sector_id', $sector->id)->update(['sector_id' => null]);
            $subsectorIds = $sector->subsectors()->pluck('id');
            if ($subsectorIds->isNotEmpty()) {
                Portfolio::whereIn('subsector_id', $subsectorIds)->update(['subsector_id' => null]);
            }
        }

        $sector->update($request->only(['language_id', 'name', 'icon', 'status', 'serial_number']));

        Session::flash('success', 'Sector updated successfully.');
        return 'success';
    }

    public function delete(Request $request)
    {
        $sector = PortfolioSector::findOrFail($request->sectorId);

        if ($sector->portfolios()->count() > 0 || $sector->subsectors()->count() > 0) {
            Session::flash('warning', 'First reassign/delete all portfolios and subsectors using this sector!');
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
            if ($sector->portfolios()->count() > 0 || $sector->subsectors()->count() > 0) {
                Session::flash('warning', 'First reassign/delete all portfolios and subsectors using the selected sectors!');
                return 'success';
            }
        }

        foreach ($ids as $id) {
            PortfolioSector::findOrFail($id)->delete();
        }

        Session::flash('success', 'Sectors deleted successfully.');
        return 'success';
    }

    /** Cascading Language -> Sectors AJAX lookup (Portfolio admin form's "Sector" select) — top-level only. */
    public function getSectors($langid)
    {
        return PortfolioSector::topLevel()->where('language_id', $langid)->where('status', 1)->orderBy('serial_number', 'asc')->get();
    }

    /** Cascading Sector -> Subsectors AJAX lookup (Portfolio admin form's "Subsector" select). */
    public function getSubsectorsForPortfolio($sectorId)
    {
        return PortfolioSector::where('parent_id', $sectorId)->where('status', 1)->orderBy('serial_number', 'asc')->get();
    }

    // ---- Subsectors (right panel) ----------------------------------------

    public function storeSubsector(Request $request)
    {
        $rules = [
            'sector_id' => 'required|exists:portfolio_sectors,id',
            'name' => 'required|max:255',
            'status' => 'required',
            'serial_number' => 'nullable|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $sector = PortfolioSector::findOrFail($request->sector_id);

        PortfolioSector::create([
            'language_id' => $sector->language_id,
            'parent_id' => $sector->id,
            'name' => $request->name,
            'status' => $request->status,
            // Defaults to "last" under this sector when left blank — matches
            // the mockup's own auto-numbered Order column.
            'serial_number' => $request->filled('serial_number')
                ? (int) $request->serial_number
                : ((int) $sector->subsectors()->max('serial_number') + 1),
        ]);

        Session::flash('success', 'New subsector added successfully.');
        return 'success';
    }

    public function updateSubsector(Request $request)
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

        PortfolioSector::whereNotNull('parent_id')->findOrFail($request->subsectorId)
            ->update($request->only(['name', 'status', 'serial_number']));

        Session::flash('success', 'Subsector updated successfully.');
        return 'success';
    }

    public function deleteSubsector(Request $request)
    {
        $subsector = PortfolioSector::whereNotNull('parent_id')->findOrFail($request->subsectorId);

        // No "reassign first" block here (unlike a top-level sector) — a
        // portfolio tagged at a deleted subsector just falls back to
        // showing its (still-valid) top-level Sector alone.
        Portfolio::where('subsector_id', $subsector->id)->update(['subsector_id' => null]);
        $subsector->delete();

        Session::flash('success', 'Subsector deleted successfully.');
        return redirect()->back();
    }

    public function toggleSubsectorStatus(Request $request)
    {
        $subsector = PortfolioSector::whereNotNull('parent_id')->findOrFail($request->subsectorId);
        $subsector->status = $subsector->status ? 0 : 1;
        $subsector->save();

        return response()->json(['success' => true, 'status' => $subsector->status]);
    }

    /** Drag-drop reorder (right panel) — full new order for one sector's subsectors, matches Highlights' own resequence pattern on the Portfolio form. */
    public function reorderSubsectors(Request $request)
    {
        $ids = $request->input('ids', []);
        // The panel only ever sends the CURRENT PAGE's ~10 rows (sortable
        // only has those in its DOM) — without this offset, reordering
        // page 2 renumbers its rows back to 1..9, colliding head-on with
        // page 1's own 1..9 and scrambling the sector's true order (every
        // later page load then sorts by serial_number with a pile of ties,
        // interleaving rows from both pages unpredictably). $offset shifts
        // the renumbering to start where this page actually begins.
        $offset = (int) $request->input('offset', 0);

        foreach ($ids as $i => $id) {
            PortfolioSector::whereNotNull('parent_id')->where('id', $id)->update(['serial_number' => $offset + $i + 1]);
        }

        return response()->json(['success' => true]);
    }
}
