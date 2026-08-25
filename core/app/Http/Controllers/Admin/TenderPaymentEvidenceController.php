<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\TenderPaymentEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenderPaymentEvidenceController extends Controller
{
    private function filtered(Request $request)
    {
        $q      = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $from   = $request->input('from');
        $to     = $request->input('to');

        // One row per ORDER — its most recent event (validate or cancel) —
        // not one row per historical event. The full validate/cancel trail
        // for an order stays intact in this same table (nothing here
        // deletes or merges rows) and shows in full on the View page's
        // Action History; this only controls what the list displays.
        $latestIds = TenderPaymentEvidence::selectRaw('MAX(id) as id')->groupBy('order_number');

        return TenderPaymentEvidence::with('purchase.tender', 'admin')
            ->whereIn('id', $latestIds)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number', 'like', "%{$q}%")
                        ->orWhereHas('purchase', function ($p) use ($q) {
                            $p->where('first_name', 'like', "%{$q}%")
                                ->orWhere('last_name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhereHas('tender', function ($t) use ($q) {
                                    $t->where('title', 'like', "%{$q}%")
                                        ->orWhere('tender_code', 'like', "%{$q}%");
                                });
                        });
                });
            })
            ->when($status === 'validated' || $status === 'canceled', function ($query) use ($status) {
                $query->where('action', $status);
            })
            ->when($from, function ($query) use ($from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                $query->whereDate('created_at', '<=', $to);
            })
            ->orderByDesc('created_at');
    }

    public function index(Request $request)
    {
        $evidences = $this->filtered($request)->paginate(20)->withQueryString();

        return view('admin.tender.evidence.index', compact('evidences'));
    }

    public function show($id)
    {
        $row = TenderPaymentEvidence::with('purchase.tender', 'admin')->findOrFail($id);

        $history = TenderPaymentEvidence::with('admin')
            ->where('order_number', $row->order_number)
            ->orderBy('created_at')
            ->get();

        return view('admin.tender.evidence.show', compact('row', 'history'));
    }

    private const HEADINGS = ['Order No.', 'User', 'Email', 'Tender', 'Amount', 'Currency', 'Payment Date', 'Status', 'Validated/Cancelled By', 'Reason', 'Proof'];

    private function rowsFor(Request $request)
    {
        return $this->filtered($request)->get();
    }

    private function toArray($row): array
    {
        $purchase = $row->purchase;

        return [
            $row->order_number,
            $purchase ? trim($purchase->first_name . ' ' . $purchase->last_name) : '',
            $purchase->email ?? '',
            $purchase && $purchase->tender ? $purchase->tender->title : '',
            $row->amount,
            $row->currency_code,
            optional($row->created_at)->format('Y-m-d H:i:s'),
            ucfirst($row->action),
            $row->admin_name,
            $row->reason,
            $row->proof_original_name,
        ];
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $rows = $this->rowsFor($request);
        if ($rows->isEmpty()) {
            Session::flash('error', 'No evidence found for the selected filters — nothing to export.');
            return back();
        }

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payment-evidence.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, self::HEADINGS);
            foreach ($rows as $row) {
                fputcsv($out, $this->toArray($row));
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'payment-evidence.csv', $headers);
    }

    public function exportWord(Request $request)
    {
        $rows = $this->rowsFor($request);
        if ($rows->isEmpty()) {
            Session::flash('error', 'No evidence found for the selected filters — nothing to export.');
            return back();
        }

        $html = view('admin.tender.evidence.export-word', ['rows' => $rows, 'headings' => self::HEADINGS])->render();

        $headers = [
            'Content-Type'        => 'application/msword',
            'Content-Disposition' => 'attachment; filename="payment-evidence.doc"',
        ];

        return response($html, 200, $headers);
    }
}
