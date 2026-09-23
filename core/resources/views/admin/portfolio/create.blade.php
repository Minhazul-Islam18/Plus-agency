@extends('admin.layout')

@section('content')
    <div class="page-header">
        <h4 class="page-title">Add Portfolio</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.portfolio.index') }}">Portfolios</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="#">Add Portfolio</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Add Portfolio</div>
                    <button type="button" id="previewBtn" class="btn btn-secondary btn-sm float-right d-inline-block mr-2">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <a class="btn btn-info btn-sm float-right d-inline-block mr-2"
                        href="{{ route('admin.portfolio.index') . '?language=' . request()->input('language') }}">
                        <span class="btn-label"><i class="fas fa-backward" style="font-size: 12px;"></i></span>
                        Back
                    </a>
                </div>
                <form id="ajaxForm" action="{{ route('admin.portfolio.store') }}" method="post">
                    @csrf
                    <div id="sliders"></div>
                    <div class="card-body pt-4 pb-4">
                        @include('admin.portfolio._form', ['portfolio' => null])
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" id="submitBtn" class="btn btn-success">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.portfolio.preview_modal')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // sectors + statuses + partners load according to language selection
            $("select[name='language_id']").on('change', function() {
                $("#sectors, #portfolioStatuses, #partnerIds").removeAttr('disabled');

                let langid = $(this).val();

                $.get("{{ url('/') }}/admin/portfolio/" + langid + "/get_sectors", function(data) {
                    let options = `<option value="" disabled selected>Ex: Energy</option>`;
                    for (let i = 0; i < data.length; i++) {
                        options += `<option value="${data[i].id}">${data[i].name}</option>`;
                    }
                    $("#sectors").html(options);
                    // #sectors is select2-enhanced (see _form.blade.php) —
                    // swapping the underlying <option>s via .html() doesn't
                    // by itself redraw select2's own closed-control text or
                    // its dropdown list; .trigger('change') tells it to
                    // resync from the DOM it now wraps.
                    $("#sectors").trigger('change');
                });

                $.get("{{ url('/') }}/admin/portfolio/" + langid + "/get_statuses", function(data) {
                    let options = `<option value="" disabled selected>Select a status</option>`;
                    for (let i = 0; i < data.length; i++) {
                        options += `<option value="${data[i].id}">${data[i].name}</option>`;
                    }
                    $("#portfolioStatuses").html(options);
                    $("#portfolioStatuses").trigger('change');
                });

                $.get("{{ url('/') }}/admin/portfolio/" + langid + "/get_partners", function(data) {
                    let options = '';
                    for (let i = 0; i < data.length; i++) {
                        options += `<option value="${data[i].id}">${data[i].name}</option>`;
                    }
                    $("#partnerIds").html(options);
                    $("#partnerIds").trigger('change');
                });
            });

            $("select[name='language_id']").on('change', function() {
                $(".request-loader").addClass("show");
                let url = "{{ url('/') }}/admin/rtlcheck/" + $(this).val();
                $.get(url, function(data) {
                    $(".request-loader").removeClass("show");
                    if (data == 1) {
                        $("form input, form select, form textarea").each(function() {
                            if (!$(this).hasClass('ltr')) $(this).addClass('rtl');
                        });
                        $("form .summernote").each(function() {
                            $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                        });
                    } else {
                        $("form input, form select, form textarea").removeClass('rtl');
                        $("form .summernote").siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
                    }
                });
            });
        });

        Dropzone.options.myDropzone = {
            acceptedFiles: '.png, .jpg, .jpeg',
            url: "{{ route('admin.portfolio.sliderstore') }}",
            maxFilesize: 2,
            success: function(file, response) {
                $("#sliders").append(`<input type="hidden" name="slider_images[]" id="slider${response.file_id}" value="${response.file_id}">`);
                var removeButton = Dropzone.createElement("<button class='rmv-btn'><i class='fa fa-times'></i></button>");
                var _this = this;
                removeButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    _this.removeFile(file);
                    rmvimg(response.file_id);
                });
                file.previewElement.appendChild(removeButton);
                if (typeof response.error != 'undefined' && typeof response.file != 'undefined') {
                    document.getElementById('errpreimg').innerHTML = response.file[0];
                }
            }
        };

        function rmvimg(fileid) {
            $.ajax({
                url: "{{ route('admin.portfolio.sliderrmv') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", fileid: fileid },
                success: function(data) { $("#slider" + fileid).remove(); }
            });
        }

        var today = new Date();
        // Calendar year range — was letting the decade/century view scroll
        // all the way back to the 1800s, which is never a real value for
        // any of these fields. bootstrap-datepicker's own `startDate`
        // option name is its MIN selectable date (confusingly, not related
        // to our "Start Date" field) — floors all three pickers at 2000.
        var minSelectableDate = new Date(2000, 0, 1);
        $("#submissionDate").datepicker({ autoclose: true, todayHighlight: true, startDate: minSelectableDate });
        $("#startDate").datepicker({ autoclose: true, startDate: minSelectableDate, endDate: today, todayHighlight: true });
        $("#endDate").datepicker({ autoclose: true, todayHighlight: true, startDate: minSelectableDate });

        // End Date can't be later than Submission Date (tender submission
        // deadline) once one is actually set — no Submission Date = no cap.
        // Mirrors PortfolioController::endDateRule(); this just stops the
        // picker UI from offering an invalid date in the first place.
        function applySubmissionDateCap() {
            var d = $('#submissionDate').datepicker('getDate');
            $('#endDate').datepicker('setEndDate', d || false);
        }
        $('#submissionDate').on('changeDate change', applySubmissionDateCap);
        applySubmissionDateCap();

        // Aperçu — POSTs the current (unsaved) form fields, renders the same
        // shared identity-card partial used by the real frontend page.
        $("#previewBtn").on('click', function() {
            let fd = new FormData(document.getElementById('ajaxForm'));
            fd.set('content', $('#portfolioContent').summernote('code'));
            $(".request-loader").addClass("show");
            $.ajax({
                url: "{{ route('admin.portfolio.preview') }}",
                method: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function(html) {
                    $(".request-loader").removeClass("show");
                    $("#portfolioPreviewBody").html(html);
                    $("#portfolioPreviewModal").modal('show');
                },
                error: function() {
                    $(".request-loader").removeClass("show");
                }
            });
        });
    </script>
@endsection
