@extends('admin.layout')

@section('styles')
    <style>
        .thumb-preview {
            position: relative;
            display: inline-block;
        }

        .thumb-preview img {
            max-width: 100%;
            max-height: 300px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }

        .thumb-preview .remove-invoice-btn,
        .thumb-preview .remove-breadcrumb-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 10;
        }

        .thumb-preview:hover .remove-invoice-btn,
        .thumb-preview:hover .remove-breadcrumb-btn {
            opacity: 1;
        }

        /* ---- Tender Settings — premium tab layout ---- */
        .tss-wrap {
            display: flex;
            align-items: flex-start;
            gap: 26px;
        }

        .tss-nav {
            flex: 0 0 260px;
            display: flex;
            flex-direction: column;
            gap: 5px;
            background: #1a2035;
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 16px;
            padding: 12px;
            position: sticky;
            top: 20px;
        }

        .tss-nav-link {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 11px 13px;
            border-radius: 12px;
            color: #b9babf;
            text-decoration: none;
            font-weight: 500;
            transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .15s ease;
        }

        .tss-nav-link:hover {
            background: rgba(255, 255, 255, .05);
            color: var(--tss-accent, #1572E8);
            text-decoration: none;
            box-shadow: none;
            transform: translateX(2px);
        }

        .tss-nav-link.active {
            background: linear-gradient(135deg, var(--tss-accent, #1572E8), var(--tss-accent-2, #4a9bff));
            color: #fff;
            box-shadow: 0 8px 18px -6px var(--tss-accent, #1572E8);
        }

        .tss-nav-icon {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            background: rgba(21, 114, 232, .12);
            background: color-mix(in srgb, var(--tss-accent, #1572E8) 14%, transparent);
            color: var(--tss-accent, #1572E8);
            transition: background .2s ease, color .2s ease;
        }

        .tss-nav-link.active .tss-nav-icon {
            background: rgba(255, 255, 255, .22);
            color: #fff;
        }

        .tss-nav-title {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.3;
        }

        .tss-nav-sub {
            display: block;
            font-size: 11px;
            font-weight: 400;
            opacity: .62;
            margin-top: 1px;
        }

        .tss-nav-link.active .tss-nav-sub {
            opacity: .85;
        }

        .tss-content {
            flex: 1 1 0%;
            min-width: 0;
            background: #1a2035;
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 16px;
            padding: 30px 32px 8px;
        }

        .tss-pane-head {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding-bottom: 20px;
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
        }

        .tss-pane-icon {
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            background: rgba(21, 114, 232, .1);
            background: color-mix(in srgb, var(--tss-accent, #1572E8) 12%, transparent);
            color: var(--tss-accent, #1572E8);
        }

        .tss-pane-title {
            font-weight: 700;
            margin-bottom: 3px;
            color: #fff;
        }

        .tss-pane-desc {
            font-size: 12.5px;
            color: #8d9498;
            margin-bottom: 0;
            line-height: 1.5;
        }

        .tss-subhead {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--tss-accent, #1572E8);
            margin: 28px 0 16px;
            padding-left: 12px;
            border-left: 3px solid var(--tss-accent, #1572E8);
        }


        @media (max-width: 767px) {
            .tss-wrap {
                flex-direction: column;
            }

            .tss-nav {
                flex-direction: row;
                overflow-x: auto;
                position: static;
                width: 100%;
            }

            .tss-nav-sub {
                display: none;
            }

            .tss-content {
                padding: 22px 18px 8px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-header">
        <h4 class="page-title">Settings</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Tenders</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="#">Settings</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Tender Settings</div>
                    <a class="btn btn-info btn-sm float-right d-inline-block"
                        href="{{ route('admin.tender.index') . '?language=' . $language }}">
                        <span class="btn-label"><i class="fas fa-backward" style="font-size:12px;"></i></span>
                        Back
                    </a>
                </div>

                <div class="card-body pt-4 pb-4">

                    <form id="settingsForm" action="{{ route('admin.tender.updateSettings') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="language" value="{{ $language }}">

                        <div class="tss-wrap">
                            <div class="tss-nav nav" id="tenderSettingsTabs" role="tablist">
                                <a class="tss-nav-link active" style="--tss-accent:#1572E8;--tss-accent-2:#4a9bff;"
                                    data-toggle="tab" href="#tab-general" role="tab">
                                    <span class="tss-nav-icon"><i class="fas fa-sliders-h"></i></span>
                                    <span>
                                        <span class="tss-nav-title">General</span>
                                        <span class="tss-nav-sub">Module on / off</span>
                                    </span>
                                </a>
                                <a class="tss-nav-link" style="--tss-accent:#6861CE;--tss-accent-2:#8f7dfb;"
                                    data-toggle="tab" href="#tab-invoice" role="tab">
                                    <span class="tss-nav-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                                    <span>
                                        <span class="tss-nav-title">Invoice Design</span>
                                        <span class="tss-nav-sub">Images &amp; footer</span>
                                    </span>
                                </a>
                                <a class="tss-nav-link" style="--tss-accent:#48ABF7;--tss-accent-2:#78c4ff;"
                                    data-toggle="tab" href="#tab-watermark" role="tab">
                                    <span class="tss-nav-icon"><i class="fas fa-stamp"></i></span>
                                    <span>
                                        <span class="tss-nav-title">Download Watermark</span>
                                        <span class="tss-nav-sub">Stamp on PDFs</span>
                                    </span>
                                </a>
                                <a class="tss-nav-link" style="--tss-accent:#F25961;--tss-accent-2:#ff8188;"
                                    data-toggle="tab" href="#tab-encryption" role="tab">
                                    <span class="tss-nav-icon"><i class="fas fa-lock"></i></span>
                                    <span>
                                        <span class="tss-nav-title">PDF Encryption</span>
                                        <span class="tss-nav-sub">Owner password</span>
                                    </span>
                                </a>
                                <a class="tss-nav-link" style="--tss-accent:#31CE36;--tss-accent-2:#5fe064;"
                                    data-toggle="tab" href="#tab-links" role="tab">
                                    <span class="tss-nav-icon"><i class="fas fa-link"></i></span>
                                    <span>
                                        <span class="tss-nav-title">Secure Links</span>
                                        <span class="tss-nav-sub">Opens allowed</span>
                                    </span>
                                </a>
                                <a class="tss-nav-link" style="--tss-accent:#FFAD46;--tss-accent-2:#ffc373;"
                                    data-toggle="tab" href="#tab-breadcrumb" role="tab">
                                    <span class="tss-nav-icon"><i class="fas fa-image"></i></span>
                                    <span>
                                        <span class="tss-nav-title">Breadcrumb</span>
                                        <span class="tss-nav-sub">Per-language banner</span>
                                    </span>
                                </a>
                            </div>

                            <div class="tss-content tab-content" id="tenderSettingsTabContent">

                                {{-- ============ General ============ --}}
                                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">

                                    <div class="tss-pane-head" style="--tss-accent:#1572E8;">
                                        <span class="tss-pane-icon"><i class="fas fa-sliders-h"></i></span>
                                        <div>
                                            <h5 class="tss-pane-title">General</h5>
                                            <p class="tss-pane-desc">Master switch for the whole Tender module.</p>
                                        </div>
                                    </div>

                                    {{-- Tender Module Toggle --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Tender Module</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_tender" value="1"
                                                    class="selectgroup-input" {{ $abex->is_tender == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Active</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="is_tender" value="0"
                                                    class="selectgroup-input" {{ $abex->is_tender == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Deactive</span>
                                            </label>
                                        </div>
                                        <p class="text-warning mb-0 mt-1" style="font-size:12px;">
                                            Enable / disable all Tender Module pages.
                                        </p>
                                    </div>

                                </div>
                                {{-- ============ /General ============ --}}

                                {{-- ============ Invoice Design ============ --}}
                                <div class="tab-pane fade" id="tab-invoice" role="tabpanel">

                                    <div class="tss-pane-head" style="--tss-accent:#6861CE;">
                                        <span class="tss-pane-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                                        <div>
                                            <h5 class="tss-pane-title">Invoice Design</h5>
                                            <p class="tss-pane-desc">Images and footer text printed on every generated
                                                invoice.</p>
                                        </div>
                                    </div>

                                    <div class="tss-subhead" style="--tss-accent:#6861CE;">Invoice Images</div>

                                    {{-- Watermark --}}
                                    <div class="form-group">
                                        <label>Watermark Image</label>
                                        <br>
                                        <div class="thumb-preview" id="thumbPreview1">
                                            @if (!empty($abex->invoice_watermark))
                                                <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_watermark) }}"
                                                    alt="Watermark" class="uploaded-img">
                                                <button type="button" class="btn btn-danger btn-sm remove-invoice-btn"
                                                    data-serial="1" data-field="invoice_watermark"
                                                    data-default="{{ asset('assets/admin/img/defaults/invoice-watermark.png') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <img src="{{ asset('assets/admin/img/defaults/invoice-watermark.png') }}"
                                                    alt="Default Watermark" class="uploaded-img" style="opacity:0.5;"
                                                    title="Default">
                                            @endif
                                        </div>
                                        <input type="hidden" name="clear_invoice_watermark" id="clearField1"
                                            value="0">
                                        <br><br>
                                        <input id="fileInput1" type="hidden" name="invoice_watermark">
                                        <button id="chooseImage1" class="choose-image btn btn-primary" type="button"
                                            data-multiple="false" data-toggle="modal" data-target="#lfmModal1">
                                            Choose Image
                                        </button>
                                        <p class="text-warning mb-0 mt-1">{{ allowed_image_extensions_label() }} images are allowed</p>
                                        <p class="text-muted mb-0"><small>Faint watermark behind fee table. Leave empty to
                                                use default.</small></p>
                                        <p class="text-warning mb-0"><small>Recommended size: 500x500px (square). Rendered at 25% of the page width in the PDF, so a simple transparent logo mark works best.</small></p>
                                    </div>

                                    {{-- Stamp / Signature --}}
                                    <div class="form-group">
                                        <label>Stamp / Signature</label>
                                        <br>
                                        <div class="thumb-preview" id="thumbPreview2">
                                            @if (!empty($abex->invoice_sign))
                                                <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_sign) }}"
                                                    alt="Signature" class="uploaded-img">
                                                <button type="button" class="btn btn-danger btn-sm remove-invoice-btn"
                                                    data-serial="2" data-field="invoice_sign"
                                                    data-default="{{ asset('assets/admin/img/defaults/invoice-sign.png') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <img src="{{ asset('assets/admin/img/defaults/invoice-sign.png') }}"
                                                    alt="Default Signature" class="uploaded-img" style="opacity:0.5;"
                                                    title="Default">
                                            @endif
                                        </div>
                                        <input type="hidden" name="clear_invoice_sign" id="clearField2" value="0">
                                        <br><br>
                                        <input id="fileInput2" type="hidden" name="invoice_sign">
                                        <button id="chooseImage2" class="choose-image btn btn-primary" type="button"
                                            data-multiple="false" data-toggle="modal" data-target="#lfmModal2">
                                            Choose Image
                                        </button>
                                        <p class="text-warning mb-0 mt-1">{{ allowed_image_extensions_label() }} images are allowed</p>
                                        <p class="text-muted mb-0"><small>Stamp/signature shown bottom-right of invoice.
                                                Leave empty to use default.</small></p>
                                        <p class="text-warning mb-0"><small>Recommended size: 400x400px (square, capped at 140x140px in the PDF). Use a transparent PNG.</small></p>
                                    </div>

                                    {{-- Footer Wavy Background --}}
                                    <div class="form-group">
                                        <label>Footer Wavy Background</label>
                                        <br>
                                        <div class="thumb-preview" id="thumbPreview3">
                                            @if (!empty($abex->invoice_footer_wavy))
                                                <img src="{{ asset('assets/admin/img/invoice/' . $abex->invoice_footer_wavy) }}"
                                                    alt="Footer Wavy" class="uploaded-img">
                                                <button type="button" class="btn btn-danger btn-sm remove-invoice-btn"
                                                    data-serial="3" data-field="invoice_footer_wavy"
                                                    data-default="{{ asset('assets/admin/img/defaults/footer-wavy.png') }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <img src="{{ asset('assets/admin/img/defaults/footer-wavy.png') }}"
                                                    alt="Default Wavy" class="uploaded-img" style="opacity:0.6;"
                                                    title="Default">
                                            @endif
                                        </div>
                                        <input type="hidden" name="clear_invoice_footer_wavy" id="clearField3"
                                            value="0">
                                        <br><br>
                                        <input id="fileInput3" type="hidden" name="invoice_footer_wavy">
                                        <button id="chooseImage3" class="choose-image btn btn-primary" type="button"
                                            data-multiple="false" data-toggle="modal" data-target="#lfmModal3">
                                            Choose Image
                                        </button>
                                        <p class="text-warning mb-0 mt-1">{{ allowed_image_extensions_label() }} images are allowed</p>
                                        <p class="text-muted mb-0"><small>Background image behind footer address. Leave
                                                empty to use default.</small></p>
                                        <p class="text-warning mb-0"><small>Recommended size: 1200x200px (6:1). Stretched to fill the footer strip exactly (not cropped), so keep close to this ratio to avoid distortion.</small></p>
                                    </div>

                                    <div class="tss-subhead" style="--tss-accent:#6861CE;">Invoice Footer</div>

                                    {{-- Footer Address --}}
                                    <div class="form-group">
                                        <label>Footer Address / Contact Info</label>
                                        <textarea name="invoice_footer_address" class="form-control summernote" rows="4">{{ old('invoice_footer_address', $abex->invoice_footer_address) }}</textarea>
                                        <small class="text-muted">Shown in the footer of every invoice. Leave empty to use
                                            the built-in default address.</small>
                                    </div>

                                </div>
                                {{-- ============ /Invoice Design ============ --}}

                                {{-- ============ Download Watermark ============ --}}
                                <div class="tab-pane fade" id="tab-watermark" role="tabpanel">

                                    <div class="tss-pane-head" style="--tss-accent:#48ABF7;">
                                        <span class="tss-pane-icon"><i class="fas fa-stamp"></i></span>
                                        <div>
                                            <h5 class="tss-pane-title">Download Watermark</h5>
                                            <p class="tss-pane-desc">
                                                Personalised, traceable watermark stamped on every <strong>PDF</strong> a
                                                buyer downloads.
                                                Applies globally (all languages). Non-PDF files are not stamped.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Enable toggle --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Watermark</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="tender_watermark_enabled" value="1"
                                                    class="selectgroup-input"
                                                    {{ $abex->tender_watermark_enabled == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Active</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="tender_watermark_enabled" value="0"
                                                    class="selectgroup-input"
                                                    {{ $abex->tender_watermark_enabled == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Deactive</span>
                                            </label>
                                        </div>
                                        <p class="text-warning mb-0 mt-1" style="font-size:12px;">
                                            When active, a PDF that fails to stamp is blocked from download (buyer is asked
                                            to contact support).
                                        </p>
                                        <a href="{{ route('admin.tender.watermarkTest') }}" target="_blank"
                                            class="btn btn-outline-info btn-sm mt-2">
                                            <i class="fas fa-vial"></i> Run watermark test (no purchase needed)
                                        </a>
                                    </div>

                                    {{-- Template --}}
                                    <div class="form-group">
                                        <label>Watermark Text</label>
                                        <textarea name="tender_watermark_template" class="form-control ltr" rows="4"
                                            placeholder="{company}&#10;Downloaded by: {name}&#10;Tender ID: {tender_code}&#10;{datetime}">{{ old('tender_watermark_template', $abex->tender_watermark_template) }}</textarea>
                                        <small class="text-muted d-block mt-1">
                                            One line each. Placeholders:
                                            <code>{company}</code> <code>{name}</code> <code>{first_name}</code>
                                            <code>{last_name}</code>
                                            <code>{tender_code}</code> <code>{tender_title}</code>
                                            <code>{order_number}</code>
                                            <code>{email}</code> <code>{datetime}</code> <code>{date}</code>.
                                            <code>{datetime}</code> is the download time in UTC. Empty lines are dropped.
                                        </small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Text Color</label>
                                                <input class="form-control jscolor ltr" name="tender_watermark_color"
                                                    value="{{ $abex->tender_watermark_color ?? 'FF0000' }}"
                                                    placeholder="FF0000">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Opacity</label>
                                                <input type="number" class="form-control ltr"
                                                    name="tender_watermark_opacity"
                                                    value="{{ $abex->tender_watermark_opacity ?? 0.3 }}" step="0.05"
                                                    min="0.05" max="1">
                                                <small class="text-muted">0.05 – 1</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Font Size</label>
                                                <input type="number" class="form-control ltr"
                                                    name="tender_watermark_font_size"
                                                    value="{{ $abex->tender_watermark_font_size ?? 24 }}" step="1"
                                                    min="6" max="96">
                                                <small class="text-muted">6 – 96 pt</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Rotation</label>
                                                <input type="number" class="form-control ltr"
                                                    name="tender_watermark_rotation"
                                                    value="{{ $abex->tender_watermark_rotation ?? 45 }}" step="1"
                                                    min="-90" max="90">
                                                <small class="text-muted">degrees (45 = diagonal)</small>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                {{-- ============ /Download Watermark ============ --}}

                                {{-- ============ PDF Encryption ============ --}}
                                <div class="tab-pane fade" id="tab-encryption" role="tabpanel">

                                    <div class="tss-pane-head" style="--tss-accent:#F25961;">
                                        <span class="tss-pane-icon"><i class="fas fa-lock"></i></span>
                                        <div>
                                            <h5 class="tss-pane-title">PDF Encryption</h5>
                                            <p class="tss-pane-desc">
                                                Lock every downloaded <strong>PDF</strong> against editing. Buyers open the
                                                file normally (no prompt),
                                                but cannot modify, annotate or fill it &mdash; those actions need the owner
                                                password below, held by admins only.
                                                Applies globally (all languages). Non-PDF files are not encrypted.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Encryption toggle --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Encryption</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="tender_pdf_encrypt_enabled" value="1"
                                                    class="selectgroup-input"
                                                    {{ $abex->tender_pdf_encrypt_enabled == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Active</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="tender_pdf_encrypt_enabled" value="0"
                                                    class="selectgroup-input"
                                                    {{ $abex->tender_pdf_encrypt_enabled == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Deactive</span>
                                            </label>
                                        </div>
                                        <p class="text-warning mb-0 mt-1" style="font-size:12px;">
                                            When active, a PDF that fails to encrypt is blocked from download (buyer is
                                            asked to contact support).
                                        </p>
                                    </div>

                                    {{-- Password --}}
                                    <div class="form-group">
                                        <label>Owner Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control ltr" name="tender_pdf_password"
                                                id="tenderPdfPassword"
                                                value="{{ old('tender_pdf_password', $abex->tender_pdf_password) }}"
                                                placeholder="Enter owner password" autocomplete="off">
                                            <div class="input-group-append">
                                                <button class="btn btn-secondary" type="button" id="togglePdfPassword"
                                                    tabindex="-1">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-1">Required when encryption is active.
                                            Admin-only &mdash; never sent to buyers. Use it to unlock editing in a PDF
                                            reader.</small>
                                    </div>

                                </div>
                                {{-- ============ /PDF Encryption ============ --}}

                                {{-- ============ Secure Links ============ --}}
                                <div class="tab-pane fade" id="tab-links" role="tabpanel">

                                    <div class="tss-pane-head" style="--tss-accent:#31CE36;">
                                        <span class="tss-pane-icon"><i class="fas fa-link"></i></span>
                                        <div>
                                            <h5 class="tss-pane-title">Secure Links</h5>
                                            <p class="tss-pane-desc">
                                                How many times each secure download link may be opened before it expires.
                                                Applies to every
                                                tender download link (email &amp; on-screen). Global (all languages).
                                            </p>
                                        </div>
                                    </div>
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="tss-subhead mt-0" style="--tss-accent:#31CE36;">Abandoned
                                                Payment Recovery</div>
                                            <p class="text-muted mb-3" style="font-size:12px;">
                                                Controls the "Complete Your Payment" email sent to a buyer whose online
                                                payment never finished and was never explicitly reported as failed by
                                                the gateway (closed the tab, gave up on an OTP prompt, network drop,
                                                browser crash). Global (all languages).
                                            </p>

                                            {{-- Payment session timeout --}}
                                            <div class="form-group">
                                                <label>Payment Session Timeout (minutes)</label>
                                                <input type="number" class="form-control ltr"
                                                    name="tender_payment_session_timeout_minutes"
                                                    value="{{ $abex->tender_payment_session_timeout_minutes ?? 5 }}"
                                                    step="1" min="1" max="1440">
                                                <small class="text-muted d-block mt-1">
                                                    Default 5. How long an online order sits Pending with no gateway
                                                    response before it's treated as abandoned and the recovery email
                                                    is sent.
                                                </small>
                                            </div>

                                            {{-- Payment link expiry --}}
                                            <div class="form-group mb-0">
                                                <label>Payment Link Expiration (hours)</label>
                                                <input type="number" class="form-control ltr"
                                                    name="tender_payment_link_expiry_hours"
                                                    value="{{ $abex->tender_payment_link_expiry_hours ?? 24 }}"
                                                    step="1" min="1" max="720">
                                                <small class="text-muted d-block mt-1">
                                                    Default 24. How long the "Complete Your Payment" / "Resume
                                                    Payment" link stays valid after being emailed, before it stops
                                                    working.
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Max downloads per link --}}
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="tss-subhead mt-0" style="--tss-accent:#31CE36;">Opens Allowed
                                                Per Link</div>
                                            <div class="form-group mb-0">
                                                <label>Opens Allowed Per Link</label>
                                                <input type="number" class="form-control ltr"
                                                    name="tender_max_downloads"
                                                    value="{{ $abex->tender_max_downloads ?? 3 }}" step="1" min="3"
                                                    max="20">
                                                <small class="text-muted d-block mt-1">Default 3 — the system
                                                    minimum, can be raised but never lowered. Each selected tender
                                                    gets its own link with its own counter.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="tss-subhead mt-0" style="--tss-accent:#31CE36;">Recovery
                                                Request Cap</div>
                                            <p class="text-muted mb-3" style="font-size:12px;">
                                                How many times a single order's link may be (re)issued per 24 hours
                                                across the <strong>Find My Files</strong> page — Order Number, OTP,
                                                Payment Reference and Regenerate all share the same budget for that
                                                order, not one each. Every issuance on any of those 4 methods counts
                                                toward it.
                                            </p>

                                    {{-- Master on/off --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Recovery Cap</label>
                                        <div class="selectgroup w-100">
                                            <label class="selectgroup-item">
                                                <input type="radio" name="tender_regen_cap_enabled" value="1"
                                                    class="selectgroup-input"
                                                    {{ $abex->tender_regen_cap_enabled == 1 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Active</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" name="tender_regen_cap_enabled" value="0"
                                                    class="selectgroup-input"
                                                    {{ $abex->tender_regen_cap_enabled == 0 ? 'checked' : '' }}>
                                                <span class="selectgroup-button">Deactive</span>
                                            </label>
                                        </div>
                                        <p class="text-warning mb-0 mt-1" style="font-size:12px;">
                                            When deactive, none of the 4 methods below enforce any cap — links can be
                                            re-issued without limit (still subject to the general rate limiter).
                                        </p>
                                    </div>

                                    {{-- Max attempts per order --}}
                                    <div class="form-group">
                                        <label>Recovery Requests Per Order (24h)</label>
                                        <input type="number" class="form-control ltr" name="tender_max_regen_per_day"
                                            value="{{ $abex->tender_max_regen_per_day ?? 3 }}" step="1"
                                            min="3" max="20">
                                        <small class="text-muted d-block mt-1">Default 3 — the system minimum, can be
                                            raised but never lowered. Only used while Recovery Cap above is
                                            Active.</small>
                                    </div>

                                    {{-- Per-method toggles --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">Apply Cap To</label>
                                        <div class="row">
                                            <div class="col-md-6 col-lg-3">
                                                <label class="d-block">Order Number</label>
                                                <div class="selectgroup w-100">
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_order_number"
                                                            value="1" class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_order_number == 1 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">On</span>
                                                    </label>
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_order_number"
                                                            value="0" class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_order_number == 0 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">Off</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <label class="d-block">OTP</label>
                                                <div class="selectgroup w-100">
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_otp" value="1"
                                                            class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_otp == 1 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">On</span>
                                                    </label>
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_otp" value="0"
                                                            class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_otp == 0 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">Off</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <label class="d-block">Payment Reference</label>
                                                <div class="selectgroup w-100">
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_payref"
                                                            value="1" class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_payref == 1 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">On</span>
                                                    </label>
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_payref"
                                                            value="0" class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_payref == 0 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">Off</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <label class="d-block">Regenerate</label>
                                                <div class="selectgroup w-100">
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_regenerate"
                                                            value="1" class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_regenerate == 1 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">On</span>
                                                    </label>
                                                    <label class="selectgroup-item">
                                                        <input type="radio" name="tender_regen_cap_regenerate"
                                                            value="0" class="selectgroup-input"
                                                            {{ $abex->tender_regen_cap_regenerate == 0 ? 'checked' : '' }}>
                                                        <span class="selectgroup-button">Off</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-2">Switch a method off to exempt it from the
                                            shared cap while leaving the others capped.</small>
                                    </div>
                                        </div>
                                    </div>

                                    {{-- Device Recognition --}}
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="tss-subhead mt-0" style="--tss-accent:#31CE36;">Device
                                                Recognition</div>
                                            <p class="text-muted mb-3" style="font-size:12px;">
                                                Maximum number of distinct devices/browsers that may be authorized
                                                per order. The first device on a link is trusted automatically; each
                                                new one after that requires an email OTP. Once this cap is reached,
                                                a new device is refused outright — see <strong>Authorized
                                                    Devices</strong> to reset or revoke devices for an order.
                                            </p>
                                            <div class="form-group mb-0">
                                                <label>Max Devices Per Order</label>
                                                <input type="number" class="form-control ltr"
                                                    name="tender_max_devices_per_order"
                                                    value="{{ $abex->tender_max_devices_per_order ?? 5 }}" step="1"
                                                    min="1" max="20">
                                                <small class="text-muted d-block mt-1">Default 5.</small>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                {{-- ============ /Secure Links ============ --}}

                                {{-- ============ Breadcrumb ============ --}}
                                <div class="tab-pane fade" id="tab-breadcrumb" role="tabpanel">

                                    <div class="tss-pane-head" style="--tss-accent:#FFAD46;">
                                        <span class="tss-pane-icon"><i class="fas fa-image"></i></span>
                                        <div>
                                            <h5 class="tss-pane-title">Breadcrumb Background</h5>
                                            <p class="tss-pane-desc">Applied to the Tenders list page and Tender Details
                                                page. Settings are per-language.</p>
                                        </div>
                                    </div>

                                    {{-- Breadcrumb BG Image --}}
                                    <div class="form-group">
                                        <label>Background Image</label>
                                        <br>
                                        <div class="thumb-preview" id="thumbPreview4">
                                            @if (!empty($abex->tender_breadcrumb_bg))
                                                <img src="{{ asset('assets/front/img/' . $abex->tender_breadcrumb_bg) }}"
                                                    alt="Breadcrumb BG" class="uploaded-img">
                                                <button type="button"
                                                    class="btn btn-danger btn-sm remove-breadcrumb-btn">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <img src="{{ asset('assets/admin/img/noimage.jpg') }}" alt="No Image"
                                                    class="uploaded-img">
                                            @endif
                                        </div>
                                        <br><br>
                                        <input id="fileInput4" type="hidden" name="tender_breadcrumb_bg">
                                        <button id="chooseImage4" class="choose-image btn btn-primary" type="button"
                                            data-multiple="false" data-toggle="modal" data-target="#lfmModal4">
                                            Choose Image
                                        </button>
                                        <p class="text-warning mb-0 mt-1">{{ allowed_image_extensions_label() }} images are allowed</p>
                                        <p class="text-info mb-0"><small><strong>Recommended size:</strong> 1920px x 350px
                                                (Width x Height)</small></p>
                                    </div>

                                    {{-- Overlay Color --}}
                                    <div class="form-group">
                                        <label>Breadcrumb Overlay Color Code</label>
                                        <input class="form-control jscolor ltr" name="tender_breadcrumb_overlay_color"
                                            value="{{ $abex->tender_breadcrumb_overlay_color ?? '000000' }}"
                                            placeholder="Enter Color Code">
                                    </div>

                                    {{-- Overlay Opacity --}}
                                    <div class="form-group">
                                        <label>Breadcrumb Overlay Opacity</label>
                                        <input type="number" class="form-control"
                                            name="tender_breadcrumb_overlay_opacity"
                                            value="{{ $abex->tender_breadcrumb_overlay_opacity ?? 0.5 }}" step="0.01"
                                            min="0" max="1" placeholder="Enter opacity (0 to 1)">
                                        <p class="text-warning mb-0">Value must be between 0 to 1 (e.g. 0.5 for 50%
                                            opacity)</p>
                                    </div>

                                </div>
                                {{-- ============ /Breadcrumb ============ --}}

                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer">
                    <div class="form-group text-center mb-0">
                        <button type="submit" form="settingsForm" class="btn btn-success">Save Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LFM Modals --}}
    @foreach ([1, 2, 3, 4] as $n)
        <div class="modal fade lfm-modal" id="lfmModal{{ $n }}" tabindex="-1" role="dialog"
            aria-hidden="true">
            <i class="fas fa-times-circle"></i>
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <iframe src="{{ url('laravel-filemanager') }}?serial={{ $n }}"
                            style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Summernote inits at 0 width while its tab is hidden (Invoice Design isn't
            // the default tab) — force the editor to recalc once that tab is shown.
            $('#tenderSettingsTabs a[href="#tab-invoice"]').on('shown.bs.tab', function() {
                $('#tab-invoice .note-editor, #tab-invoice .note-editing-area, #tab-invoice .note-editable')
                    .css('width', '100%');
            });

            // PDF password show / hide
            $('#togglePdfPassword').on('click', function() {
                var $inp = $('#tenderPdfPassword');
                var toText = $inp.attr('type') === 'password';
                $inp.attr('type', toText ? 'text' : 'password');
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });

            // Invoice image remove (client-side: revert to default, flag for server clear)
            $(document).on('click', '.remove-invoice-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var serial = $btn.data('serial');
                var defSrc = $btn.data('default');

                swal({
                    title: 'Are you sure?',
                    text: 'This will revert to the default image.',
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: 'Cancel',
                            visible: true
                        },
                        confirm: {
                            text: 'Yes, remove it!',
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then(function(willDelete) {
                    if (willDelete) {
                        $('#thumbPreview' + serial + ' img').attr('src', defSrc).css('opacity',
                            0.5);
                        $btn.remove();
                        $('#fileInput' + serial).val('');
                        $('#clearField' + serial).val('1');
                    }
                });
            });

            // Breadcrumb image remove (AJAX delete)
            $(document).on('click', '.remove-breadcrumb-btn', function(e) {
                e.preventDefault();
                swal({
                    title: 'Are you sure?',
                    text: 'Delete this breadcrumb background image?',
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: 'Cancel',
                            visible: true
                        },
                        confirm: {
                            text: 'Yes, delete it!',
                            closeModal: false
                        }
                    },
                    dangerMode: true,
                }).then(function(willDelete) {
                    if (willDelete) {
                        $.ajax({
                            url: '{{ route('admin.tender.deleteTenderBreadcrumbBg') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                language: '{{ $language }}'
                            },
                            success: function(res) {
                                swal.close();
                                if (res.success) {
                                    $('#thumbPreview4 img').attr('src',
                                        '{{ asset('assets/admin/img/noimage.jpg') }}'
                                    );
                                    $('.remove-breadcrumb-btn').remove();
                                    $('#fileInput4').val('');
                                    $.notify({
                                        message: 'Background image deleted.'
                                    }, {
                                        type: 'success'
                                    });
                                }
                            },
                            error: function() {
                                swal.close();
                                $.notify({
                                    message: 'Delete failed.'
                                }, {
                                    type: 'danger'
                                });
                            }
                        });
                    }
                });
            });

        });
    </script>
    @if ($errors->any())
        <script>
            $(document).ready(function() {
                var fieldToTab = {
                    invoice_footer_address: '#tab-invoice',
                    tender_watermark_opacity: '#tab-watermark',
                    tender_watermark_font_size: '#tab-watermark',
                    tender_watermark_rotation: '#tab-watermark',
                    tender_watermark_color: '#tab-watermark',
                    tender_watermark_template: '#tab-watermark',
                    tender_pdf_encrypt_enabled: '#tab-encryption',
                    tender_pdf_password: '#tab-encryption',
                    tender_max_downloads: '#tab-links',
                    tender_payment_session_timeout_minutes: '#tab-links',
                    tender_payment_link_expiry_hours: '#tab-links',
                    tender_breadcrumb_overlay_color: '#tab-breadcrumb',
                    tender_breadcrumb_overlay_opacity: '#tab-breadcrumb',
                };
                var errorFields = @json(array_keys($errors->toArray()));
                for (var i = 0; i < errorFields.length; i++) {
                    var tab = fieldToTab[errorFields[i]];
                    if (tab) {
                        $('#tenderSettingsTabs a[href="' + tab + '"]').tab('show');
                        break;
                    }
                }
            });
        </script>
    @endif
@endsection
