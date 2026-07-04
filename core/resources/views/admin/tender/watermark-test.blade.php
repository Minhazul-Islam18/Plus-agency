@extends('admin.layout')

@section('content')
<div class="page-header">
  <h4 class="page-title">Watermark Test</h4>
  <ul class="breadcrumbs">
    <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a></li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Tenders</a></li>
    <li class="separator"><i class="flaticon-right-arrow"></i></li>
    <li class="nav-item"><a href="#">Watermark Test</a></li>
  </ul>
</div>

<div class="row">
  <div class="col-md-10 offset-md-1">
    <div class="card">
      <div class="card-header">
        <div class="card-title d-inline-block">
          {{ $mode === 'cleanup' ? 'Cleanup Result' : 'Test Result' }}
        </div>
        <a href="{{ route('admin.tender.settings') }}" class="btn btn-info btn-sm float-right">Back to Settings</a>
      </div>
      <div class="card-body">

        @php
          // Pull the two download URLs out of the command output.
          preg_match('/Direct ZIP:\s*(\S+)/', $output, $mZip);
          preg_match('/Confirm page:\s*(\S+)/', $output, $mConfirm);
          $zipUrl     = $mZip[1] ?? null;
          $confirmUrl = $mConfirm[1] ?? null;
        @endphp

        @if ($mode !== 'cleanup' && $zipUrl)
          <div class="alert alert-success">
            <strong>Test data seeded.</strong> Click below to download the watermarked PDF.
          </div>
          <p>
            <a href="{{ $zipUrl }}" target="_blank" class="btn btn-success">
              <i class="fas fa-download"></i> Download watermarked ZIP
            </a>
            <a href="{{ $confirmUrl }}" target="_blank" class="btn btn-outline-secondary">
              Open confirm page
            </a>
          </p>
          <p class="text-muted" style="font-size:13px;">
            Open the PDF inside the ZIP — you should see a full document with the diagonal
            watermark on top. When finished testing:
          </p>
          <a href="{{ route('admin.tender.watermarkTestCleanup') }}" class="btn btn-danger btn-sm"
            onclick="return confirm('Remove all watermark-test data?');">
            <i class="fas fa-trash"></i> Clean up test data
          </a>
        @elseif ($mode === 'cleanup')
          <div class="alert alert-info"><strong>Cleanup done.</strong> Test data removed.</div>
          <a href="{{ route('admin.tender.watermarkTest') }}" class="btn btn-primary btn-sm">Run test again</a>
        @else
          <div class="alert alert-warning">
            Could not read the download link from the command output. See raw output below.
            Common causes: SetaPDF library / license not uploaded, or ionCube not enabled for this PHP version.
          </div>
        @endif

        <hr>
        <label class="font-weight-bold">Raw output</label>
        <pre style="background:#f7f7f9;border:1px solid #e1e1e8;border-radius:4px;padding:12px;white-space:pre-wrap;">{{ trim($output) ?: '(no output)' }}</pre>
      </div>
    </div>
  </div>
</div>
@endsection
