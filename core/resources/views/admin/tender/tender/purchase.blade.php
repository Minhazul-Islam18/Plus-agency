@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Tenders</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Tenders</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Purchases</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="card-title d-inline-block">Purchases</div>
                        </div>

                        <div class="col-lg-9 mt-2 mt-lg-0">
                            <div class="d-flex float-right">
                                <button class="btn btn-danger btn-sm d-none bulk-delete mr-2"
                                    data-href="{{ route('admin.tender.purchaseBulkOrderDelete') }}">
                                    <i class="flaticon-interface-5"></i> Delete
                                </button>

                                {{-- Language filter --}}
                                <select name="language" class="form-control form-control-sm mr-2" style="width: auto;"
                                    onchange="window.location='{{ route('admin.tender.purchaseLog') }}?language='+this.value+'&order_number={{ request()->input('order_number') }}&registration_no={{ request()->input('registration_no') }}'">
                                    <option value="">All Languages</option>
                                    @foreach ($langs as $lang)
                                        <option value="{{ $lang->code }}"
                                            {{ request()->input('language') == $lang->code ? 'selected' : '' }}>
                                            {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Order number search --}}
                                <form action="{{ route('admin.tender.purchaseLog') }}" method="GET" class="d-flex mr-2">
                                    <input type="hidden" name="language" value="{{ request()->input('language') }}">
                                    <input type="hidden" name="registration_no" value="{{ request()->input('registration_no') }}">
                                    <input name="order_number" type="text" class="form-control form-control-sm"
                                        placeholder="Search Order Number" value="{{ request()->input('order_number') }}">
                                </form>

                                {{-- Company registration number search --}}
                                <form action="{{ route('admin.tender.purchaseLog') }}" method="GET" class="d-flex">
                                    <input type="hidden" name="language" value="{{ request()->input('language') }}">
                                    <input type="hidden" name="order_number" value="{{ request()->input('order_number') }}">
                                    <input name="registration_no" type="text" class="form-control form-control-sm"
                                        placeholder="Search Registration No." value="{{ request()->input('registration_no') }}">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            @if (count($purchases) == 0)
                                <h3 class="text-center">NO ENROLL FOUND</h3>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped mt-3" style="table-layout: fixed; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th scope="col" style="width: 40px;">
                                                    <input type="checkbox" class="bulk-check" data-val="all">
                                                </th>
                                                <th scope="col" style="width: 120px;">Order Number</th>
                                                <th scope="col" style="width: 190px;">Tender</th>
                                                <th scope="col" style="width: 140px;">Name</th>
                                                <th scope="col" style="width: 130px;">Payment Status</th>
                                                <th scope="col" style="width: 90px;">Access</th>
                                                <th scope="col" style="width: 140px;">Receipt</th>
                                                <th scope="col" style="width: 90px;">Details</th>
                                                <th scope="col" style="width: 260px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($purchases as $purchase)
                                                <tr>
                                                    <td style="width: 40px;">
                                                        <input type="checkbox" class="bulk-check"
                                                            data-val="{{ $purchase->id }}">
                                                    </td>
                                                    <td style="width: 120px; word-break: break-word;">{{ $purchase->order_number }}</td>
                                                    <td style="width: 190px;">
                                                        @if (!empty($purchase->tender))
                                                            @php $tTitle = $purchase->tender->title; @endphp
                                                            @if (mb_strlen($tTitle, 'utf-8') > 30)
                                                                <span class="tender-title-short">{{ mb_substr($tTitle, 0, 30, 'utf-8') }}&hellip;</span><span class="tender-title-full d-none">{{ $tTitle }}</span>
                                                                <a href="javascript:void(0)" class="tender-title-toggle" style="font-size: 11px; white-space: nowrap;">Show more</a>
                                                            @else
                                                                {{ $tTitle }}
                                                            @endif
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td style="width: 140px; word-break: break-word;">{{ $purchase->first_name }} {{ $purchase->last_name }}</td>
                                                    <td style="width: 130px;">
                                                        @if (strtolower($purchase->payment_status) == 'completed')
                                                            <span class="badge badge-success">Completed</span>
                                                        @else
                                                            <span class="badge badge-danger">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td style="width: 90px;">
                                                        @if ($purchase->isSuspended())
                                                            <span class="badge badge-dark" data-toggle="tooltip"
                                                                title="{{ $purchase->suspend_reason ?: 'Suspended by admin' }}">Suspended</span>
                                                        @else
                                                            <span class="badge badge-success">Active</span>
                                                        @endif
                                                    </td>
                                                    <td style="width: 140px;">
                                                        <div>
                                                            @if (!empty($purchase->invoice))
                                                                <a href="{{ route('admin.tender.invoiceDownload', $purchase->id) }}"
                                                                    target="_blank" class="btn btn-success btn-sm"
                                                                    data-toggle="tooltip" title="Download Invoice">
                                                                    <i class="fas fa-download"></i> Invoice
                                                                </a>
                                                            @elseif (strtolower($purchase->payment_status) === 'completed')
                                                                <form
                                                                    action="{{ route('admin.tender.purchaseGenerateInvoice', $purchase->id) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-outline-success btn-sm">
                                                                        Generate Invoice
                                                                    </button>
                                                                </form>
                                                            @else
                                                                -
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td style="width: 90px;">
                                                        <a href="#" class="btn btn-primary btn-sm" data-toggle="modal"
                                                            data-target="#detailsModal{{ $purchase->id }}">
                                                            Details
                                                        </a>
                                                    </td>
                                                    <td style="width: 260px;">
                                                        <div class="d-flex flex-row flex-wrap align-items-center" style="gap: 6px;">
                                                            {{-- Suspend / Reactivate this transaction --}}
                                                            <form action="{{ route('admin.tender.purchaseSuspend') }}"
                                                                method="POST" class="suspendform m-0">
                                                                @csrf
                                                                <input type="hidden" name="purchase_id"
                                                                    value="{{ $purchase->id }}">
                                                                @if ($purchase->isSuspended())
                                                                    <button type="submit"
                                                                        class="btn btn-success btn-sm m-0">
                                                                        <i class="fas fa-unlock mr-1"></i> Reactivate
                                                                    </button>
                                                                @else
                                                                    <button type="submit"
                                                                        class="suspendbtn btn btn-warning btn-sm m-0">
                                                                        <i class="fas fa-ban mr-1"></i> Suspend
                                                                    </button>
                                                                @endif
                                                            </form>

                                                            {{-- Quick blacklist this company (keyed on its registration no.) --}}
                                                            @if (\App\TenderBlacklist::matches($purchase->company_registration_no))
                                                                <a href="{{ route('admin.tender.blacklist') }}"
                                                                    class="btn btn-secondary btn-sm m-0"
                                                                    data-toggle="tooltip" title="Already blacklisted — manage on the Blacklist page">
                                                                    <i class="fas fa-user-slash mr-1"></i> Blacklisted
                                                                </a>
                                                            @else
                                                                <form
                                                                    action="{{ route('admin.tender.blacklist.fromPurchase') }}"
                                                                    method="POST" class="blacklistform m-0">
                                                                    @csrf
                                                                    <input type="hidden" name="purchase_id"
                                                                        value="{{ $purchase->id }}">
                                                                    <input type="hidden" name="reason" value="">
                                                                    <button type="submit"
                                                                        class="blacklistbtn btn btn-dark btn-sm m-0">
                                                                        <i class="fas fa-user-slash mr-1"></i> Blacklist
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            <form class="deleteform m-0"
                                                                action="{{ route('admin.tender.purchaseDelete') }}"
                                                                method="POST">
                                                                @csrf
                                                                <input type="hidden" name="purchase_id"
                                                                    value="{{ $purchase->id }}">
                                                                <button type="submit"
                                                                    class="deletebtn btn btn-danger btn-sm m-0">
                                                                    <i class="fas fa-trash mr-1"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Per-row modals rendered OUTSIDE the table on purpose: a
                                     <form> whose start tag appears as a direct child of
                                     <table> (not inside a <td>) hits a special HTML5
                                     parsing rule that inserts the <form> then immediately
                                     pops it off the open-elements stack — its own children
                                     (hidden inputs, submit button) never end up nested
                                     inside it in the parsed DOM, so `.closest('.revertform')`
                                     silently finds nothing and the JS submit throws. Keeping
                                     these includes here (after </table>) avoids that
                                     entirely instead of relying on browsers to "recover"
                                     from invalid table content. --}}
                                @foreach ($purchases as $purchase)
                                    @includeIf('admin.tender.tender.receipt')
                                    @includeIf('admin.tender.tender.purchase-details')
                                    @includeIf('admin.tender.tender.markpaid-modal')
                                    @includeIf('admin.tender.tender.proof-modal')
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="d-inline-block mx-auto">
                            {{ $purchases->appends([
                                    'language' => request()->input('language'),
                                    'order_number' => request()->input('order_number'),
                                    'registration_no' => request()->input('registration_no'),
                                ])->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tender-title-full {
            word-break: break-word;
            white-space: normal;
        }
    </style>
    <script>
        // Details → Mark as Paid → LFM is 3 levels of nested modal on this
        // page (the tender edit form's own LFM modal never has this problem —
        // it opens directly from a full page, only 1 level deep). Bootstrap 4
        // has no built-in support for more than one open modal at a time: by
        // default a second .modal('show') call steals the single shared
        // backdrop, so the first modal visually vanishes even though it's
        // still "open" in the DOM. This raises each nested modal's own
        // z-index (and its own backdrop's, right below it) so they stack
        // properly instead — closing the top one reveals the one underneath,
        // same as the browser's own window stacking, no custom hide/show
        // relay needed between them.
        //
        // jQuery isn't guaranteed to be defined yet at this point on every
        // admin page (load-order varies) — bind immediately if it's already
        // there, otherwise wait for window 'load' (fires only after every
        // <script src> including jQuery has finished) instead of throwing.
        (function() {
            function bindModalStacking($) {
                $(document).on('show.bs.modal', '.modal', function() {
                    var zIndex = 1050 + (10 * $('.modal.show').length);
                    $(this).css('z-index', zIndex);
                    setTimeout(function() {
                        $('.modal-backdrop').not('.modal-stacked').css('z-index', zIndex - 1).addClass('modal-stacked');
                    });
                });
            }
            function bindProofFilenameLabel($) {
                // LFM writes the picked file straight into #fileInput{serial}
                // itself (see markpaid-modal.blade.php) — no callback fires,
                // so the only reliable moment to react is when its own modal
                // finishes closing (file picked, or cancelled via the X).
                $(document).on('hidden.bs.modal', '[id^="lfmModal"]', function() {
                    var serial = this.id.replace('lfmModal', '');
                    var url = $('#fileInput' + serial).val();
                    var $label = $('#proofFileName' + serial);
                    if (!url || !$label.length) return;
                    var name = decodeURIComponent(url.split('/').pop().split('?')[0]);
                    $label.html('<span class="text-success"><i class="fas fa-check-circle mr-1"></i>' + name + '</span>');
                });
            }
            function bindRevertReason($) {
                // Same reason-prompt pattern as .blacklistbtn (custom.js),
                // except the reason is mandatory here — cancelling a payment
                // validation revokes the buyer's access immediately, so
                // there must be a record of why.
                $(document).on('click', '.revertbtn', function(e) {
                    e.preventDefault();
                    var form = $(this).closest('.revertform');
                    // This button lives inside the open "Details" Bootstrap
                    // modal — Bootstrap's own focus-trap (_enforceFocus)
                    // keeps yanking focus back into that modal on every
                    // focusin, which fires for SweetAlert's input too (it's
                    // appended to <body>, outside the modal), making it
                    // impossible to type. Bootstrap re-attaches this handler
                    // the next time any modal is shown, so it's safe to drop
                    // it here rather than track re-enabling it after close.
                    $(document).off('focusin.bs.modal');
                    swal({
                        title: 'Cancel this payment validation?',
                        text: 'Download access will be revoked immediately and the buyer notified. A reason is required.',
                        content: {
                            element: 'input',
                            attributes: { placeholder: 'Reason for cancelling (required)', type: 'text' }
                        },
                        buttons: {
                            confirm: { text: 'Yes, cancel it', className: 'btn btn-danger' },
                            cancel: { visible: true, className: 'btn btn-secondary' }
                        }
                    }).then(function(reason) {
                        if (reason === null) { swal.close(); return; }
                        if (!reason.trim()) {
                            $.notify({ message: 'A reason is required to cancel this payment validation.' }, {
                                type: 'danger', placement: { from: 'top', align: 'right' }
                            });
                            return;
                        }
                        form.find('input[name="reason"]').val(reason.trim());
                        $(".request-loader").addClass("show");
                        form.get(0).submit();
                    });
                });
            }
            if (window.jQuery) {
                bindModalStacking(window.jQuery);
                bindProofFilenameLabel(window.jQuery);
                bindRevertReason(window.jQuery);
            } else {
                window.addEventListener('load', function() {
                    if (window.jQuery) {
                        bindModalStacking(window.jQuery);
                        bindProofFilenameLabel(window.jQuery);
                        bindRevertReason(window.jQuery);
                    }
                });
            }
        })();

        document.addEventListener('click', function(e) {
            var toggle = e.target.closest('.tender-title-toggle');
            if (!toggle) return;

            var cell = toggle.closest('td');
            var short = cell.querySelector('.tender-title-short');
            var full = cell.querySelector('.tender-title-full');
            var expanded = !full.classList.contains('d-none');

            short.classList.toggle('d-none', !expanded);
            full.classList.toggle('d-none', expanded);
            toggle.textContent = expanded ? 'Show more' : 'Show less';
        });
    </script>
@endsection
