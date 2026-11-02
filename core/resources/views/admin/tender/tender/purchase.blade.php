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
                                                        @elseif (strtolower($purchase->payment_status) == 'rejected')
                                                            <span class="badge badge-danger" data-toggle="tooltip"
                                                                title="{{ $purchase->rejection_reason }}">Rejected</span>
                                                        @else
                                                            <span class="badge badge-warning">Pending</span>
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
                                                        <a href="#" class="btn btn-primary btn-sm js-purchase-details"
                                                            data-id="{{ $purchase->id }}">
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
                                                            @if (isset($blacklisted[\App\TenderPurchase::normalizeRegNo($purchase->company_registration_no)]))
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

                                {{-- One shared Details modal, filled over AJAX on click
                                     (purchaseDetails()). It sits OUTSIDE the table on purpose:
                                     a <form> that is a direct child of <table> is dropped by
                                     the HTML5 parser, which breaks `.closest('.revertform')`.
                                     The Validate / Proof modals the details open are injected
                                     into #purchaseExtraModals as siblings (not nested) so the
                                     modal-stacking script below keeps working. --}}
                                <div class="modal fade" id="purchaseDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Receipt details <small class="text-muted" id="purchaseDetailsOrder"></small></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="purchaseDetailsBody"></div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="purchaseExtraModals"></div>
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

    @php
        // Mirrors the real Details layout: label (col-5) / value (col-7) rows
        // separated by <hr>, in the same order. Label bars are near full
        // width like the bold labels; value bars vary; the payment-status
        // row gets pill-sized bars like its badge + button.
        $pdRows = [
            [78, 62], [92, 38], [40, 45], [38, 40], [52, 70], [54, 46], [36, 30], [28, 34],
            [58, 55], [88, 40], [64, 78], [30, 12], [66, 48], [70, 'pill'], [62, 52], [48, 32],
        ];
    @endphp
    <template id="purchaseDetailsSkeleton">
        <div class="container pd-skeleton" role="status" aria-live="polite" aria-label="Loading details">
            @foreach ($pdRows as [$lw, $vw])
                <div class="row">
                    <div class="col-lg-5"><span class="pd-sk" style="width: {{ $lw }}%"></span></div>
                    <div class="col-lg-7">
                        @if ($vw === 'pill')
                            <span class="pd-sk pd-sk-pill"></span><span class="pd-sk pd-sk-btn"></span>
                        @else
                            <span class="pd-sk" style="width: {{ $vw }}%"></span>
                        @endif
                    </div>
                </div>
                <hr>
            @endforeach
        </div>
    </template>

    <style>
        .pd-skeleton .pd-sk {
            position: relative;
            display: block;
            height: 14px;
            margin: 3px 0;
            border-radius: 4px;
            overflow: hidden;
            background: #e9edf2;
        }
        .pd-skeleton .pd-sk::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .85), transparent);
            animation: pd-shimmer 1.2s ease-in-out infinite;
        }
        .pd-skeleton .pd-sk-pill,
        .pd-skeleton .pd-sk-btn {
            display: inline-block;
            height: 22px;
            margin: 0;
            vertical-align: middle;
        }
        .pd-skeleton .pd-sk-pill { width: 72px; border-radius: 12px; margin-right: 8px; }
        .pd-skeleton .pd-sk-btn { width: 130px; border-radius: 4px; }
        @keyframes pd-shimmer {
            to { transform: translateX(100%); }
        }
        /* Reduced motion: no sweep, but still a gentle pulse so it reads as "loading". */
        @media (prefers-reduced-motion: reduce) {
            .pd-skeleton .pd-sk::after { display: none; }
            .pd-skeleton .pd-sk { animation: pd-pulse 1.6s ease-in-out infinite; }
        }
        @keyframes pd-pulse {
            50% { opacity: .45; }
        }
        /* ---- Validate Payment modal (dark panel) ---- */
        .vp-modal .modal-content {
            background: radial-gradient(120% 80% at 100% 0%, #14264a 0%, #0b1426 55%, #09101f 100%);
            color: #e6ecf7;
            border: 1px solid rgba(120, 150, 220, .25);
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(0, 0, 0, .55);
        }
        .vp-modal .modal-header, .vp-modal .modal-footer { border-color: rgba(255, 255, 255, .08); }
        .vp-modal .modal-title { color: #fff; font-weight: 600; }
        .vp-modal .modal-title i { color: #4d8dff; margin-right: 8px; }
        .vp-modal .close { color: #fff; opacity: .8; text-shadow: none; }
        .vp-card {
            border: 1.5px solid rgba(77, 141, 255, .45);
            border-radius: 14px;
            background: rgba(17, 33, 66, .55);
            overflow: hidden;
        }
        .vp-card-head {
            padding: 12px 18px;
            font-weight: 600;
            color: #fff;
            background: rgba(77, 141, 255, .12);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }
        .vp-card-head i { color: #4d8dff; margin-right: 10px; }
        .vp-card-head-lg { font-size: 17px; }
        .vp-kv {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 18px;
        }
        .vp-kv + .vp-kv { border-top: 1px solid rgba(255, 255, 255, .08); }
        .vp-k { color: #cdd7ea; }
        .vp-k i { color: #6ea3ff; width: 22px; margin-right: 8px; text-align: center; }
        .vp-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 5px 14px; border-radius: 999px; font-weight: 600; font-size: 13px;
        }
        .vp-pill-method { background: rgba(88, 80, 200, .35); color: #fff; border: 1px solid rgba(140, 130, 255, .35); }
        .vp-pill-method b {
            display: inline-flex; align-items: center; justify-content: center;
            width: 26px; height: 26px; border-radius: 50%;
            background: #ffd400; color: #111; font-size: 10px;
        }
        .vp-pill-yes { background: rgba(16, 160, 100, .3); color: #7dffc0; border: 1px solid rgba(60, 220, 150, .4); }
        .vp-pill-no { background: rgba(160, 160, 160, .2); color: #cfd6e4; border: 1px solid rgba(255, 255, 255, .2); }
        .vp-receipt-title { color: #fff; font-weight: 600; margin: 4px 0 10px; }
        .vp-receipt-title i { color: #4d8dff; margin-right: 10px; }
        .vp-receipt {
            display: block; border: 2px solid rgba(255, 255, 255, .85); border-radius: 10px;
            overflow: hidden; background: #0b1426; max-height: 230px;
        }
        .vp-receipt img { display: block; width: 100%; max-height: 226px; object-fit: cover; object-position: top; }
        .vp-receipt-file {
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
            height: 150px; color: #cfe0ff; text-decoration: none; border-style: dashed;
        }
        .vp-receipt-file i { font-size: 34px; color: #6ea3ff; }
        .vp-receipt-file:hover { color: #fff; }
        .vp-receipt-missing { color: #ffb4b4; border-color: rgba(255, 120, 120, .6); }
        .vp-receipt-missing i { color: #ff8a8a; }
        .vp-rows { padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 8px; }
        .vp-row {
            display: flex; align-items: stretch;
            border: 1px solid rgba(255, 255, 255, .22); border-radius: 12px;
            background: rgba(255, 255, 255, .03);
        }
        .vp-row-k {
            flex: 0 0 46%; display: flex; align-items: center; gap: 12px;
            padding: 9px 14px; font-weight: 600; color: #fff;
            border-right: 1px solid rgba(255, 255, 255, .22);
        }
        .vp-row-k i { color: #4d8dff; width: 22px; text-align: center; font-size: 16px; }
        .vp-row-v { flex: 1; display: flex; align-items: center; padding: 9px 16px; color: #dfe7f5; word-break: break-word; }
        .vp-instr { padding: 14px 18px; max-height: 220px; overflow: auto; color: #dfe7f5; font-size: 14px; }
        .vp-instr * { color: inherit !important; background: transparent !important; }
        .vp-strip {
            display: grid; grid-template-columns: 1fr 0.8fr 1.5fr;
            border: 1px solid rgba(255, 255, 255, .14); border-radius: 14px;
            background: rgba(17, 33, 66, .45);
        }
        .vp-strip-cell { display: flex; gap: 14px; padding: 16px 18px; }
        .vp-strip-cell + .vp-strip-cell { border-left: 1px solid rgba(255, 255, 255, .14); }
        .vp-strip-ico { color: #6ea3ff; font-size: 24px; margin-top: 2px; }
        .vp-strip-title { display: block; color: #fff; font-weight: 600; margin-bottom: 4px; }
        .vp-strip-sub { color: #cdd7ea; font-size: 13px; margin-bottom: 4px; }
        .vp-strip-val { color: #e6ecf7; word-break: break-word; }
        .vp-strip-amount { font-weight: 700; font-size: 16px; color: #fff; }
        .vp-req { color: #ff6b6b; }
        .vp-note {
            width: 100%; resize: vertical; min-height: 54px; padding: 8px 10px;
            color: #fff; background: rgba(0, 0, 0, .25);
            border: 1px solid rgba(255, 255, 255, .18); border-radius: 8px; font-size: 13px;
        }
        .vp-note::placeholder { color: rgba(255, 255, 255, .5); }
        .vp-note:focus { outline: 0; border-color: #4d8dff; box-shadow: 0 0 0 2px rgba(77, 141, 255, .25); }
        .vp-btn-cancel {
            color: #fff; background: transparent; border: 1.5px solid rgba(255, 255, 255, .55);
            border-radius: 8px; padding: 9px 26px; font-weight: 600;
        }
        .vp-btn-cancel:hover { background: rgba(255, 255, 255, .1); color: #fff; }
        .vp-btn-confirm {
            color: #fff; border: 0; border-radius: 8px; padding: 9px 26px; font-weight: 600;
            background: linear-gradient(180deg, #1fb34a, #0f8a35);
            box-shadow: 0 6px 18px rgba(20, 170, 70, .35);
        }
        .vp-btn-confirm:hover { color: #fff; filter: brightness(1.1); }
        @media (max-width: 767px) {
            .vp-strip { grid-template-columns: 1fr; }
            .vp-strip-cell + .vp-strip-cell { border-left: 0; border-top: 1px solid rgba(255, 255, 255, .14); }
            .vp-row { flex-direction: column; }
            .vp-row-k { flex-basis: auto; border-right: 0; border-bottom: 1px solid rgba(255, 255, 255, .22); }
        }
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
            function bindRejectReason($) {
                // Mandatory reason — it is emailed to the buyer so they know
                // what to fix before resubmitting.
                $(document).on('click', '.rejectbtn', function(e) {
                    e.preventDefault();
                    var form = $(this).closest('.rejectform');
                    $(document).off('focusin.bs.modal');
                    swal({
                        title: 'Reject this receipt?',
                        text: 'The buyer is emailed this reason with a link to submit a corrected receipt.',
                        content: {
                            element: 'input',
                            attributes: { placeholder: 'Reason shown to the buyer (required)', type: 'text' }
                        },
                        buttons: {
                            confirm: { text: 'Reject receipt', className: 'btn btn-danger' },
                            cancel: { visible: true, className: 'btn btn-secondary' }
                        }
                    }).then(function(reason) {
                        if (reason === null) { swal.close(); return; }
                        if (!reason.trim()) {
                            $.notify({ message: 'A reason is required to reject a receipt.' }, {
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
            function bindValidateConfirm($) {
                // Final confirmation before an approval goes out — it emails the
                // buyer their download link and invoice. The form is injected via
                // AJAX, hence the delegated handler.
                $(document).on('submit', '.vp-form', function(e) {
                    var form = this;
                    if (form.dataset.confirmed === '1') { return; }
                    e.preventDefault();
                    if (!form.checkValidity()) { form.reportValidity(); return; }
                    $(document).off('focusin.bs.modal');
                    swal({
                        title: 'Confirm payment?',
                        text: 'Are you sure you want to validate this payment? This confirms a payment of ' +
                            form.dataset.amount + ' and emails the buyer their download link and invoice.',
                        icon: 'warning',
                        buttons: {
                            cancel: { visible: true, text: 'Cancel', className: 'btn btn-secondary' },
                            confirm: { text: 'Yes, confirm payment', className: 'btn btn-success' }
                        }
                    }).then(function(ok) {
                        if (!ok) { return; }
                        form.dataset.confirmed = '1';
                        $('.request-loader').addClass('show');
                        form.submit();
                    });
                });
            }
            function bindPurchaseDetails($) {
                var urlTpl = @json(route('admin.tender.purchaseDetails', ['id' => '__ID__']));
                var $modal = $('#purchaseDetailsModal');
                var $body = $('#purchaseDetailsBody');
                var $extras = $('#purchaseExtraModals');
                var xhr = null;

                function showSkeleton() {
                    $body.html(document.getElementById('purchaseDetailsSkeleton').innerHTML);
                    $('#purchaseDetailsOrder').text('');
                }

                function load(id) {
                    if (xhr) { xhr.abort(); }
                    showSkeleton();
                    $extras.empty();
                    var req = xhr = $.ajax({ url: urlTpl.replace('__ID__', id), dataType: 'json' });
                    req.done(function(res) {
                        $('#purchaseDetailsOrder').text('— ' + res.order_number);
                        $body.html(res.details);
                        $extras.html(res.extras);
                        $body.find('[data-toggle="tooltip"]').tooltip();
                    }).fail(function(jq, status) {
                        if (status === 'abort') { return; }
                        $body.html('<div class="text-center py-5"><p class="text-danger mb-3">Could not load the details.</p>' +
                            '<button type="button" class="btn btn-outline-primary btn-sm js-purchase-details-retry" data-id="' + id + '">Try again</button></div>');
                    });
                }

                $(document).on('click', '.js-purchase-details', function(e) {
                    e.preventDefault();
                    $modal.modal('show');
                    load($(this).data('id'));
                });
                $(document).on('click', '.js-purchase-details-retry', function() {
                    load($(this).data('id'));
                });
                // "View / Hide Validation Message" label follows the collapse state.
                $(document).on('show.bs.collapse hide.bs.collapse', '[id^="vnote"]', function(e) {
                    var open = e.type === 'show';
                    var $a = $('.js-vnote[href="#' + this.id + '"]');
                    $a.find('.vn-show').toggleClass('d-none', open);
                    $a.find('.vn-hide').toggleClass('d-none', !open);
                });
                // Drop the previous order's content so the next open never flashes it.
                $modal.on('hidden.bs.modal', function() {
                    if (xhr) { xhr.abort(); }
                    $body.empty();
                    $extras.empty();
                });
            }
            if (window.jQuery) {
                bindPurchaseDetails(window.jQuery);
                bindValidateConfirm(window.jQuery);
                bindModalStacking(window.jQuery);
                bindRevertReason(window.jQuery);
                bindRejectReason(window.jQuery);
            } else {
                window.addEventListener('load', function() {
                    if (window.jQuery) {
                        bindPurchaseDetails(window.jQuery);
                        bindValidateConfirm(window.jQuery);
                        bindModalStacking(window.jQuery);
                        bindRevertReason(window.jQuery);
                        bindRejectReason(window.jQuery);
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
