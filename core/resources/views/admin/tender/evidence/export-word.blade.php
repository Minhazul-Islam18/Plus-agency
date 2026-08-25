<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Payment Evidence</title>
<style>
    body { font-family: Calibri, Arial, sans-serif; font-size: 12px; }
    h2 { margin-bottom: 4px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
    th { background: #f2f2f2; }
</style>
</head>
<body>
    <h2>Payment Evidence Tracking</h2>
    <p>Exported {{ now()->format('d M Y, H:i') }}</p>
    <table>
        <thead>
            <tr>
                @foreach ($headings as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php
                    $purchase = $row->purchase;
                @endphp
                <tr>
                    <td>{{ $row->order_number }}</td>
                    <td>{{ $purchase ? trim($purchase->first_name . ' ' . $purchase->last_name) : '' }}</td>
                    <td>{{ $purchase->email ?? '' }}</td>
                    <td>{{ $purchase && $purchase->tender ? $purchase->tender->title : '' }}</td>
                    <td>{{ $row->amount }}</td>
                    <td>{{ $row->currency_code }}</td>
                    <td>{{ optional($row->created_at)->format('Y-m-d H:i:s') }}</td>
                    <td>{{ ucfirst($row->action) }}</td>
                    <td>{{ $row->admin_name }}</td>
                    <td>{{ $row->reason }}</td>
                    <td>{{ $row->proof_original_name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
