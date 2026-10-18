{{--
    Minimal social-share-image picker — same LFM thumb/fileInput/modal
    convention as everywhere else in admin, without the SEO-title/live-
    preview machinery seo-meta-box.blade.php carries (the pages that use
    this already have their own title/description fields inline).

    Required: $ogSerial (int, unique on the page), $ogFieldName (string)
    Optional: $ogCurrentUrl (string|null)
--}}
@php
    $ogCurrentUrl = $ogCurrentUrl ?? null;
@endphp
<div class="form-group">
    <label>Social Share Image (Optional)</label>
    <br>
    <div class="thumb-preview" id="thumbPreview{{ $ogSerial }}">
        <img src="{{ $ogCurrentUrl ?: asset('assets/admin/img/noimage.jpg') }}" alt="Social share image">
    </div>
    <br>
    <input id="fileInput{{ $ogSerial }}" type="hidden" name="{{ $ogFieldName }}">
    <button id="chooseImage{{ $ogSerial }}" class="choose-image btn btn-primary" type="button"
        data-multiple="false" data-toggle="modal" data-target="#lfmModal{{ $ogSerial }}">Choose Image</button>
    <p class="text-warning mb-0"><small>Falls back to the global Default Social Share Image (Admin → Scripts) when left unset.</small></p>

    <div class="modal fade lfm-modal" id="lfmModal{{ $ogSerial }}" tabindex="-1" role="dialog" aria-hidden="true">
        <i class="fas fa-times-circle"></i>
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <iframe src="{{ url('laravel-filemanager') }}?serial={{ $ogSerial }}"
                        style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>
