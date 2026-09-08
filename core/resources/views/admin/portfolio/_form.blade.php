{{--
    Shared Portfolio create/edit form fields — the "evidence of competence"
    3-column redesign (see ~/Documents/15-Portoflios/Recommandation sur
    Portofolios-*.docx). Included by create.blade.php (with $portfolio = null)
    and edit.blade.php (with the real $portfolio).

    Expects: $portfolio (nullable), $services, $sectors, $statuses, $countries, $bex.
--}}
@php
    $isEdit = !empty($portfolio);
    $old = function ($field, $default = '') use ($portfolio, $isEdit) {
        return old($field, $isEdit ? $portfolio->{$field} ?? $default : $default);
    };
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" integrity="sha384-aQuvIWWIbpu/mSqULLDiUveyYiPoJzPKAWjUmGJ+Elm+N/LJhzfZqsutsfw870JS" crossorigin="anonymous">

<style>
    /* Matches the app's own dark admin theme (body[data-background-color="dark"]
       .card => #202940, .card-title => #fff) instead of a hardcoded light
       panel — a light bg here left labels using the theme's light-on-dark
       default color, unreadable against it. A real border + shadow (the
       flat bg-only version blended straight into the page behind it) plus
       a proper header strip is what actually reads as "separate cards"
       instead of one continuous grey field. */
    .portfolio-form-col {
        background: #202940;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        padding: 22px;
        height: 100%;
        box-shadow: 0 12px 30px -14px rgba(0, 0, 0, 0.5);
    }

    .portfolio-form-col h5 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14.5px;
        font-weight: 700;
        letter-spacing: 0.01em;
        color: #fff;
        margin: -22px -22px 22px;
        padding: 16px 22px;
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px 10px 0 0;
    }

    .portfolio-form-col h5 i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 7px;
        background: rgba(22, 185, 153, 0.16);
        color: #16b999;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* .form-group label's site-wide default (#495057, atlantis.min.css) is
       tuned for a light card background — barely readable against this
       dark one. Scoped here only, not touching the global rule other
       (light-background) admin pages already rely on. */
    .portfolio-form-col .form-group label {
        color: #ccd2e3;
    }

    .portfolio-block-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-top: 14px;
    }

    @media (max-width: 1199px) {
        .portfolio-block-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 767px) {
        .portfolio-block-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Divides "Project Details" into Identity / Classification / Timeline
       sub-groups — a heading-less rule reads as a lighter break than
       another h5, appropriate one level down inside a column that already
       has its own heading. */
    .portfolio-subdivider {
        border: none;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        margin: 4px 0 18px;
    }

    .portfolio-block {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        padding: 12px;
    }

    .portfolio-block label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 6px;
        color: #8b92a9;
    }

    .portfolio-block-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }

    /* Icon-picker trigger (same fontawesome-iconpicker plugin used by
       Approach/Statistics/Features elsewhere in the admin) — the selected-
       icon preview and the dropdown-open caret, sized up so the current
       icon actually reads at a glance instead of a tiny unreadable chip. */
    .portfolio-icon-picker .btn-group {
        display: inline-flex;
    }

    .portfolio-icon-picker .iconpicker-component {
        width: 42px;
        height: 34px;
        padding: 0;
        font-size: 17px;
    }

    .portfolio-icon-picker .icp-dd {
        height: 34px;
        padding: 0 12px;
        font-size: 15px;
    }

    /* The icon-picker popover ships with its own light-theme CSS (white/
       #f7f7f7 chrome, dark text) — normally fine on its own, but this page
       also loads the front-end's dark-glass.css (for the Aperçu preview
       elsewhere on this same form), and that stylesheet has a blanket,
       unscoped `input[type="search"], .form-control {...}` rule with
       !important that overrides the search box's background to a
       near-transparent glass tint while its *text* stays whatever this
       page's own body color is (white) — white-on-near-white, unreadable.
       Rather than patch the leak at its source (dark-glass.css needs that
       generic rule for real front-end inputs), reskin the whole popover
       dark to match the admin theme — consistent with the rest of this
       form instead of just papering over the one broken input. */
    .iconpicker-popover.popover {
        background: #1a2035 !important;
        border: 1px solid #2f374b !important;
        color: #fff;
        box-shadow: 0 12px 30px -10px rgba(0, 0, 0, 0.6);
    }
    .iconpicker-popover .iconpicker-items {
        max-height: 360px !important;
    }
    .iconpicker-popover .iconpicker-item {
        /* Sized to keep 4 per row inside the (reverted-to-default) 234px
           popover width — bigger than the plugin default 14px, but not
           so big it drops to 3 per row. */
        width: 16px !important;
        height: 16px !important;
        padding: 12px !important;
        font-size: 15px !important;
    }
    .iconpicker-popover .popover-title {
        background: #202940 !important;
        border-bottom: 1px solid #2f374b !important;
        color: #fff;
    }
    .iconpicker-popover input[type="search"].iconpicker-search {
        background: #131a2c !important;
        border: 1px solid #2f374b !important;
        color: #fff !important;
    }
    .iconpicker-popover input[type="search"].iconpicker-search::placeholder {
        color: rgba(255, 255, 255, 0.4);
    }
    .iconpicker-popover .iconpicker-items {
        background: #1a2035 !important;
    }
    .iconpicker-popover .iconpicker-item {
        box-shadow: 0 0 0 1px #2f374b !important;
        color: #cfd4e4;
    }
    .iconpicker-popover .iconpicker-item:hover:not(.iconpicker-selected) {
        background-color: rgba(255, 255, 255, 0.08) !important;
    }
    .iconpicker-popover .iconpicker-item.iconpicker-selected {
        background: #1572e8 !important;
        color: #fff;
    }
    .iconpicker-popover .popover-footer {
        background: #202940 !important;
        border-top: 1px solid #2f374b !important;
    }

    /* Country <select> upgraded to select2 — a plain native <select> with
       176 countries in raw alphabetical order (no search, no flags) is
       what "not a proper select at all" meant. select2's default theme is
       also unstyled light-on-white (the same class of bug as the
       icon-picker popover fixed earlier) since neither this page nor the
       site-wide init pass `theme:'bootstrap'` — reskinned dark here
       rather than touching the shared global init. */
    #portfolioCountrySelect + .select2-container,
    #services + .select2-container,
    #sectors + .select2-container,
    #portfolioStatuses + .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        background: #1a2035 !important;
        border: 1px solid #2f374b !important;
        border-radius: 4px;
        height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff;
        line-height: calc(2.25rem) !important;
        padding-left: 12px;
        /* A long service title (or country name) must not stretch the
           closed control past its own width — clip it instead. */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: rgba(255, 255, 255, 0.4);
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: rgba(255, 255, 255, 0.5) transparent transparent transparent;
    }
    .select2-dropdown {
        background: #1a2035 !important;
        border: 1px solid #2f374b !important;
        color: #fff;
    }
    .select2-search--dropdown .select2-search__field {
        background: #131a2c !important;
        border: 1px solid #2f374b !important;
        color: #fff !important;
    }
    .select2-container--default .select2-results__option {
        color: #cfd4e4;
        /* select2's dropdown panel always matches the closed control's own
           width (unlike a native <select>'s popup, which grows to fit its
           widest option) — this is what actually fixes "dropdown grows
           wide with big text". Left to wrap/overflow by default though, so
           still needs its own clip+ellipsis for a title longer than that
           fixed width. */
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #1572e8 !important;
        color: #fff;
    }
    .select2-container--default .select2-results__option[aria-selected="true"] {
        background: rgba(255, 255, 255, 0.08);
    }
    .select2-results__option .fi {
        margin-right: 8px;
        border-radius: 2px;
    }
</style>

<div class="row">
    <div class="col-lg-12">
        <div class="portfolio-form-col">
            <h5><i class="fas fa-file-alt"></i> {{ __('General Information') }}</h5>

            <div class="form-group">
                <label>{{ __('Project Title') }} **</label>
                <input type="text" class="form-control" name="title" value="{{ $old('title') }}" required
                    placeholder="{{ __('Ex: Call for tenders for 15 kV and 33 kV mobile substations') }}">
                <p id="errtitle" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group">
                <label>{{ __('Detailed Article') }} **</label>
                <textarea id="portfolioContent" class="form-control summernote" name="content" required
                    placeholder="{{ __('Describe the project in detail…') }}" data-height="300">{{ $isEdit ? replaceBaseUrl($portfolio->content) : $old('content') }}</textarea>
                <p id="errcontent" class="mb-0 text-danger em"></p>
            </div>

            <div class="form-group">
                <label>{{ __('Meta Keywords') }}</label>
                <input class="form-control" name="meta_keywords" value="{{ $old('meta_keywords') }}"
                    placeholder="Enter meta keywords" data-role="tagsinput">
            </div>
            <div class="form-group mb-0">
                <label>{{ __('Meta Description') }}</label>
                <textarea class="form-control" name="meta_description" rows="4" placeholder="Enter meta description">{{ $old('meta_description') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12">
        <div class="portfolio-form-col">
            <h5><i class="fas fa-folder-open"></i> {{ __('Project Details') }}</h5>

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Client') }} **</label>
                        <input type="text" class="form-control" name="client_name" value="{{ $old('client_name') }}"
                            data-role="tagsinput" required placeholder="Ex: SONABEL">
                        <p id="errclient_name" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Sector') }}</label>
                        <select id="sectors" class="form-control" name="sector_id" {{ $isEdit ? '' : 'disabled' }}>
                            <option value="" {{ $old('sector_id') ? '' : 'selected' }} disabled>
                                {{ __('Ex: Energy') }}</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}"
                                    {{ $old('sector_id') == $sector->id ? 'selected' : '' }}>
                                    {{ convertUtf8($sector->name) }}</option>
                            @endforeach
                        </select>
                        <p id="errsector_id" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Country') }}</label>
                        <select id="portfolioCountrySelect" class="form-control" name="country">
                            <option value="" {{ $old('country') ? '' : 'selected' }} disabled>Ex: Burkina Faso
                            </option>
                            @foreach ($countries as $c)
                                <option value="{{ $c['iso'] }}"
                                    {{ $old('country') == $c['iso'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                            @endforeach
                        </select>
                        <p id="errcountry" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Year') }}</label>
                        <input type="text" class="form-control ltr" name="year" value="{{ $old('year') }}"
                            placeholder="Ex: 2026">
                        <p id="erryear" class="mb-0 text-danger em"></p>
                    </div>
                </div>
            </div>

            <hr class="portfolio-subdivider">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Status') }} **</label>
                        <select id="portfolioStatuses" class="form-control ltr" name="status_id" required {{ $isEdit ? '' : 'disabled' }}>
                            <option value="" {{ $old('status_id') ? '' : 'selected' }} disabled>
                                {{ __('Select a status') }}</option>
                            @foreach ($statuses as $s)
                                <option value="{{ $s->id }}"
                                    {{ $old('status_id') == $s->id ? 'selected' : '' }}>
                                    {{ convertUtf8($s->name) }}</option>
                            @endforeach
                        </select>
                        <p id="errstatus_id" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Potential partners') }}</label>
                        <input type="text" class="form-control" name="partners" value="{{ $old('partners') }}"
                            placeholder="{{ __('Ex: Partner names') }}">
                        <p id="errpartners" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Service') }} **</label>
                        <select id="services" class="form-control" name="service_id" required
                            {{ $isEdit ? '' : 'disabled' }}>
                            <option value="" selected disabled>Select a service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}"
                                    {{ $old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->title }}
                                </option>
                            @endforeach
                        </select>
                        <p id="errservice_id" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Cost of Service') }} ({{ $bex->base_currency_symbol }})</label>
                        <input type="number" class="form-control" name="cost_of_service" id="cost_of_service"
                            value="{{ $old('cost_of_service') }}" placeholder="Enter cost of service" step="0.01"
                            min="0">
                        <p id="errcost_of_service" class="mb-0 text-danger em"></p>
                    </div>
                </div>
            </div>

            <hr class="portfolio-subdivider">

            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Start Date') }}</label>
                        <input id="startDate" type="text" class="form-control datepicker" name="start_date"
                            value="{{ $old('start_date') }}" placeholder="Enter start date" autocomplete="off">
                        <p id="errstart_date" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <label>{{ __('Submission Date') }}</label>
                        <input id="submissionDate" type="text" class="form-control datepicker"
                            name="submission_date" value="{{ $old('submission_date') }}"
                            placeholder="Enter submission date" autocomplete="off">
                        <p id="errsubmission_date" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group mb-0">
                        <label>{{ __('Website Link') }}</label>
                        <input type="url" class="form-control" name="website_link" value="{{ $old('website_link') }}"
                            placeholder="Enter website link">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-12">
        <div class="portfolio-form-col">
            <h5><i class="fas fa-images"></i> {{ __('Media') }}</h5>

            <div class="row">
            <div class="col-lg-3">
            {{-- Featured / main image (serial=1) --}}
            <div class="form-group">
                <label>{{ __('Main Image') }} **</label>
                <br>
                <div class="thumb-preview" id="thumbPreview1">
                    <img src="{{ $isEdit && $portfolio->featured_image ? asset('assets/front/img/portfolios/featured/' . $portfolio->featured_image) : asset('assets/admin/img/noimage.jpg') }}"
                        alt="Featured Image">
                </div>
                <br><br>
                <input id="fileInput1" type="hidden" name="image">
                <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false"
                    data-toggle="modal" data-target="#lfmModal1">{{ __('Click to upload an image') }}</button>
                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                <p class="em text-danger mb-0" id="errimage"></p>

                <div class="modal fade lfm-modal" id="lfmModal1" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe src="{{ url('laravel-filemanager') }}?serial=1&callback=lfmNoop"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3">
            {{-- Client logo (serial=4) --}}
            <div class="form-group">
                <label>{{ __('Client logo') }}</label>
                <br>
                <div class="thumb-preview" id="thumbPreview4">
                    <img src="{{ $isEdit && $portfolio->client_logo ? asset('assets/front/img/portfolios/logos/' . $portfolio->client_logo) : asset('assets/admin/img/noimage.jpg') }}"
                        alt="Client Logo">
                </div>
                <br><br>
                <input id="fileInput4" type="hidden" name="client_logo">
                <button id="chooseImage4" class="choose-image btn btn-primary" type="button" data-multiple="false"
                    data-toggle="modal" data-target="#lfmModal4">{{ __('Choose a logo') }}</button>
                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>

                <div class="modal fade lfm-modal" id="lfmModal4" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe src="{{ url('laravel-filemanager') }}?serial=4&callback=lfmNoop"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3">
            {{-- Gallery (serial=2, existing) --}}
            <div class="form-group">
                <label>{{ __('Image Gallery') }} **</label>
                <br>
                <div class="slider-thumbs" id="sliderThumbs2"></div>
                <input id="fileInput2" type="hidden" name="slider" value="" />
                <button id="chooseImage2" class="choose-image btn btn-primary" type="button" data-multiple="true"
                    data-toggle="modal" data-target="#lfmModal2">{{ __('Add images') }}</button>
                <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                <p id="errslider" class="mb-0 text-danger em"></p>

                <div class="modal fade lfm-modal" id="lfmModal2" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe id="lfmIframe2"
                                    src="{{ url('laravel-filemanager') }}?serial=2&callback=lfmNoop{{ $isEdit ? '&portfolio=' . $portfolio->id : '' }}"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <div class="col-lg-3">
            {{-- Documents (serial=3, LFM "file" category) --}}
            <div class="form-group">
                <label>{{ __('Documents (optional)') }}</label>
                <br>
                <div class="slider-thumbs" id="sliderThumbs3"></div>
                <input id="fileInput3" type="hidden" name="documents" value="" />
                <button id="chooseImage3" class="choose-image btn btn-primary" type="button" data-multiple="true"
                    data-toggle="modal" data-target="#lfmModal3">{{ __('Add documents') }}</button>
                <p class="text-warning mb-0">Formats: PDF, DOC, DOCX, XLSX</p>
                <p id="errdocuments" class="mb-0 text-danger em"></p>

                <div class="modal fade lfm-modal" id="lfmModal3" tabindex="-1" role="dialog" aria-hidden="true">
                    <i class="fas fa-times-circle"></i>
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body p-0">
                                <iframe id="lfmIframe3"
                                    src="{{ url('laravel-filemanager') }}?type=file&serial=3&callback=lfmNoop{{ $isEdit ? '&documents=' . $portfolio->id : '' }}"
                                    style="width:100%;height:500px;overflow:hidden;border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>{{ __('Language') }} **</label>
                        <select id="language" name="language_id" class="form-control" {{ $isEdit ? '' : 'required' }}
                            {{ $isEdit ? 'disabled' : '' }}>
                            <option value="" {{ $old('language_id') ? '' : 'selected' }} disabled>Select a
                                language</option>
                            @foreach ($langs as $lang)
                                <option value="{{ $lang->id }}"
                                    {{ $old('language_id') == $lang->id ? 'selected' : '' }}>{{ $lang->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($isEdit)
                            <input type="hidden" name="language_id" value="{{ $portfolio->language_id }}">
                        @endif
                        <p id="errlanguage_id" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>{{ __('Serial Number') }} **</label>
                        <input type="number" class="form-control ltr" name="serial_number" required
                            value="{{ $old('serial_number') }}" placeholder="Enter Serial Number">
                        <p id="errserial_number" class="mb-0 text-danger em"></p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>{{ __('Visibility') }}</label>
                        @php $isPublishedVal = $old('is_published', 1); @endphp
                        <select class="form-control ltr" name="is_published">
                            <option value="1" {{ $isPublishedVal == 1 ? 'selected' : '' }}>{{ __('Published') }}
                            </option>
                            <option value="0" {{ $isPublishedVal == 0 ? 'selected' : '' }}>{{ __('Unpublished') }}
                            </option>
                        </select>
                        <p class="text-warning mb-0"><small>{{ __('The project will be visible on the website.') }}</small>
                        </p>
                    </div>
                </div>
            </div>

            <input type="text" class="form-control d-none" name="tags" value="{{ $old('tags') }}">
        </div>
    </div>
</div>

{{-- Evidence of Competence — the 6 structured reference blocks (see doc:
     Problem -> ICA Mission -> Expertise -> Approach/Solution -> Result ->
     Impact). Its own full-width row, not squeezed into the "Project
     Details" column — these are a distinct set of long-form fields, not
     project metadata, and six of them cramped into a 1/3-width column
     made that column tower over the other two. Plain textareas, one
     bullet per non-empty line on render — no rich text needed here. --}}
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="portfolio-form-col">
            <h5><i class="fas fa-layer-group"></i> {{ __('Evidence of Competence') }}</h5>

            @php
                // Each block's icon is admin-chosen (picked from the same
                // icon-picker library used elsewhere in this admin — Approach
                // points, Statistics, Features, ...), stored in its own
                // <field>_icon column. Falls back to the block's original
                // hardcoded icon for rows saved before this existed.
                $blockIcons = [
                    'problematique' => 'fas fa-bullseye',
                    'mission_ica' => 'fas fa-file-signature',
                    'expertise_mobilisee' => 'fas fa-cogs',
                    'solution_approche' => 'fas fa-lightbulb',
                    'resultat_statut' => 'fas fa-chart-bar',
                    'impact' => 'fas fa-users',
                ];
            @endphp

            <div class="portfolio-block-grid">
                @foreach ([
                    ['field' => 'problematique', 'label' => 'Problem', 'rows' => 3, 'placeholder' => 'Describe the problem or the client\'s need…'],
                    ['field' => 'mission_ica', 'label' => 'Mission entrusted to ICA', 'rows' => 3, 'placeholder' => 'Describe the mission entrusted to ICA…'],
                    ['field' => 'expertise_mobilisee', 'label' => 'Expertise mobilized', 'rows' => 3, 'placeholder' => 'List the expertise mobilized by ICA… (one per line)'],
                    ['field' => 'solution_approche', 'label' => 'Solution / Approach', 'rows' => 3, 'placeholder' => 'Describe the solution or approach used… (one per line)'],
                    ['field' => 'resultat_statut', 'label' => 'Result / Status', 'rows' => 3, 'placeholder' => 'Present the results obtained or the current status… (one per line)'],
                    ['field' => 'impact', 'label' => 'Impact', 'rows' => 3, 'placeholder' => 'Specify the project\'s impact (if relevant)… (one per line)'],
                ] as $blk)
                    @php $blkIcon = $old($blk['field'] . '_icon', $blockIcons[$blk['field']]); @endphp
                    <div class="portfolio-block">
                        <div class="portfolio-block-head">
                            <label class="mb-0">{{ __($blk['label']) }}</label>
                            <div class="portfolio-icon-picker">
                                <div class="btn-group d-block">
                                    <button type="button" class="btn btn-sm btn-secondary iconpicker-component" tabindex="-1"
                                        title="{{ __('Choose an icon') }}"><i class="{{ $blkIcon }}"></i></button>
                                    <button type="button" class="icp icp-dd btn btn-sm btn-secondary dropdown-toggle"
                                        data-toggle="dropdown"></button>
                                    <div class="dropdown-menu"></div>
                                </div>
                                <input type="hidden" class="portfolio-icon-input" name="{{ $blk['field'] }}_icon"
                                    value="{{ $blkIcon }}">
                            </div>
                        </div>
                        <textarea class="form-control" name="{{ $blk['field'] }}" rows="{{ $blk['rows'] }}"
                            placeholder="{{ __($blk['placeholder']) }}">{{ $old($blk['field']) }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
    // Every LFM iframe on this page (main image, logo, gallery, documents)
    // already has its own working "Confirm" handler — see the app's own
    // override at resources/views/vendor/laravel-filemanager/index.blade.php
    // (a `data-action="use"` click listener that correctly fills
    // #fileInput<serial>/#sliderThumbs<serial>). The problem: that
    // override doesn't stop the STOCK LFM script (assets/lfm/js/script.js)
    // from ALSO handling the very same click via its own generic
    // `[data-action]` delegate — and the stock handler's `use()` function,
    // finding no `?callback=` param and no `window.opener` (this is an
    // iframe, not a popup), falls through to `window.open(item.url)` —
    // opening the just-picked file's raw front-end URL in a new tab right
    // after selecting it. That's the "it's again uploading in site!"
    // report: nothing actually re-uploads, but a document/image opening in
    // a fresh tab right after you picked it reads exactly like that.
    //
    // Passing `callback=lfmNoop` on each iframe's src reroutes that stock
    // fallback: `use()` finds `parent[callback]` (this function) truthy,
    // calls it (a deliberate no-op) instead of `window.open`, and marks
    // itself "succeeded" — which also skips its own `window.close()` call
    // (harmless here since `window.opener` is null anyway). The real,
    // working selection handling above is untouched either way.
    window.lfmNoop = function () {};
</script>

<script>
    // Wires up the 6 icon-pickers above. custom.js already auto-inits
    // every ".icp-dd" via `$('.icp-dd').iconpicker();` on page ready — the
    // one thing that pattern doesn't handle is more than one instance on
    // the same page (its own examples elsewhere all target a single
    // hardcoded #inputIcon). Scoping each picker to its own
    // .portfolio-icon-picker wrapper via closest() instead of a shared ID
    // is what lets all 6 coexist.
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.jQuery) return;
        var $ = window.jQuery;
        $('.portfolio-icon-picker').each(function () {
            var $wrap = $(this);
            var savedIcon = $wrap.find('.portfolio-icon-input').val();
            function applySavedIcon() {
                if (savedIcon) $wrap.find('.iconpicker-component i').attr('class', savedIcon);
            }
            // custom.js's own `.iconpicker()` call (no `selected` option
            // passed) resets the preview <i> to blank as its LAST init
            // step, then fires "iconpickerCreated" — listening for that
            // (attached here, before custom.js's ready-queued init call
            // actually runs) re-applies the real saved icon right after,
            // instead of guessing at a timeout that runs before or after.
            $wrap.find('.icp').on('iconpickerCreated', applySavedIcon);
            // Belt-and-braces in case "iconpickerCreated" already fired
            // by the time this attaches (init order isn't contractual).
            setTimeout(applySavedIcon, 0);

            $wrap.find('.icp').on('iconpickerSelected', function () {
                var iconClass = $wrap.find('.iconpicker-component i').attr('class');
                $wrap.find('.portfolio-icon-input').val(iconClass);
            });
        });

        // Country <select> — searchable, with a flag next to each name.
        // The option's own `value` IS the ISO code already, so the flag
        // template is a direct lookup — no emoji-parsing needed (unlike
        // the #waCode dial-code picker elsewhere in the admin, which
        // decodes a flag emoji baked into its option text instead).
        function countryFlagTemplate(opt) {
            if (!opt.id) return opt.text;
            var iso = opt.id.toLowerCase();
            return $(
                '<span><span class="fi fi-' + iso + '"></span> ' + opt.text + '</span>'
            );
        }
        // Deferred to window 'load' — verified by instrumentation (logging
        // every $.fn.select2 call with a timestamp) that a plain
        // setTimeout(fn, 0) here was NOT enough: custom.js's own generic
        // `$('.select2').select2();` (its jQuery-ready handler) actually
        // fired ~66ms AFTER our setTimeout(0) callback in testing — later
        // than the very next tick, for reasons not worth chasing further
        // (jQuery's internal ready-deferred resolution isn't a plain
        // synchronous DOMContentLoaded listener). Since select2's
        // generated wrapper span carries "select2" as one of ITS OWN
        // classes, that generic pass running AFTER ours exists matches
        // the CONTAINER and re-initializes IT as if it were a select —
        // corrupting it (collapsed to 1px, hidden, unreadable — the
        // original bug report). 'load' is the one point genuinely
        // guaranteed later than any jQuery-ready handler on the page
        // (DOMContentLoaded/ready always resolve before 'load' fires, by
        // spec) — creating our container only then means there is
        // nothing left on the page that could still re-catch it.
        window.addEventListener('load', function () {
            $('#portfolioCountrySelect').select2({
                width: '100%',
                templateResult: countryFlagTemplate,
                templateSelection: countryFlagTemplate,
            });

            // Service <select> — some titles are long tender names ("Ex:
            // Call for tenders for 15 kV and 33 kV mobile substations").
            // A plain native <select>'s dropdown popup grows to fit its
            // widest option (no CSS can stop that); select2's own dropdown
            // always matches the closed control's width instead, so a long
            // title just gets clipped with an ellipsis (CSS above) rather
            // than blowing the popup out wide. Starts disabled on the
            // create page (enabled once a language is picked, same as
            // #sectors/#portfolioStatuses) — select2 renders fine on a
            // disabled select and picks up the enable via its own
            // attribute observer once create.blade.php's cascade removes
            // `disabled`, same as it already does for `<option>` swaps.
            $('#services').select2({ width: '100%' });

            // Sector / Status — same treatment: same disabled-until-
            // language-picked lifecycle, same reskin, same width fix.
            $('#sectors').select2({ width: '100%' });
            $('#portfolioStatuses').select2({ width: '100%' });
        });
    });
</script>

<script>
    // Every field flagged ** above is `required` in the backend validator
    // (PortfolioController@store/@update) — this enforces the same list on
    // the client so a missing field is caught instantly instead of only
    // after a round trip. Plain HTML5 `required` attributes don't work
    // here: custom.js's #submitBtn handler is a plain 'click' listener that
    // builds the AJAX request directly (it doesn't go through the form's
    // native submit/constraint-validation step at all), and two of these
    // fields (image, slider) are `type="hidden"` — the spec bars hidden
    // inputs from constraint validation entirely regardless of `required`.
    //
    // Registered on `document` with capture:true so it always runs BEFORE
    // custom.js's own bubble-phase listener on #submitBtn — capturing
    // listeners on an ancestor fire before any listener on the target
    // itself, no matter which script tag ran first. Calling
    // stopPropagation() here when invalid stops the event before it ever
    // reaches that handler, so no AJAX call goes out.
    document.addEventListener('click', function (e) {
        if (!e.target || e.target.id !== 'submitBtn' || !window.jQuery) return;
        var $ = window.jQuery;

        var isEdit = {{ $isEdit ? 'true' : 'false' }};
        var required = [
            { name: 'title' },
            { name: 'content', summernote: true },
            { name: 'client_name' },
            { name: 'service_id' },
            { name: 'status_id' },
            { name: 'serial_number' },
            // Always resubmitted in full on save (see storeDocuments/
            // update() comments) — required on create AND edit.
            { name: 'slider' },
        ];
        if (!isEdit) {
            required.push({ name: 'image' }, { name: 'language_id' });
        }

        $('.em').each(function () { $(this).html(''); });

        var firstInvalid = null;
        required.forEach(function (f) {
            var value;
            if (f.summernote) {
                // custom.js's own submit handlers (#submitBtn/#updateBtn)
                // locate the summernote instance the same way — by its
                // `.summernote` class, not `#portfolioContent` — because
                // this build reassigns the textarea's `id` to a random
                // string on init (confirmed live: renders as e.g.
                // `_5o536vi2e`), leaving only the `name` and class stable.
                value = $('#ajaxForm .summernote').summernote('code').replace(/<[^>]*>/g, '').trim();
            } else {
                value = ($('[name="' + f.name + '"]').first().val() || '').toString().trim();
            }
            if (!value) {
                var $err = $('#err' + f.name);
                if ($err.length) $err.html('{{ __('This field is required.') }}');
                if (!firstInvalid) firstInvalid = f.name;
            }
        });

        if (firstInvalid) {
            e.preventDefault();
            e.stopPropagation();
            $.notify({
                message: '{{ __('Please fill in all required fields.') }}',
                title: '{{ __('Validation Error!') }}',
                icon: 'fa fa-bell',
            }, {
                type: 'danger',
                placement: { from: 'top', align: 'right' },
                showProgressbar: true,
                time: 1000,
                allow_dismiss: true,
                delay: 4000,
            });
        }
    }, true);
</script>
