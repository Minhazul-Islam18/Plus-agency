@extends('admin.layout')

@if (!empty($portfolio->language) && $portfolio->language->rtl == 1)
    @section('styles')
        <style>
            form input, form textarea, form select { direction: rtl; }
        </style>
    @endsection
@endif

@section('content')
    <div class="page-header">
        <h4 class="page-title">Edit Portfolio</h4>
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
                <a href="#">Edit</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-inline-block">Edit Portfolio</div>
                    <button type="button" id="previewBtn" class="btn btn-secondary btn-sm float-right d-inline-block mr-2">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <a class="btn btn-info btn-sm float-right d-inline-block mr-2"
                        href="{{ route('admin.portfolio.index') . '?language=' . request()->input('language') }}">
                        <span class="btn-label"><i class="fas fa-backward" style="font-size: 12px;"></i></span>
                        Back
                    </a>
                </div>
                <form id="ajaxForm" action="{{ route('admin.portfolio.update') }}" method="post">
                    @csrf
                    <input type="hidden" name="portfolio_id" value="{{ $portfolio->id }}">
                    <div class="card-body pt-4 pb-4">
                        @include('admin.portfolio._form', ['portfolio' => $portfolio])
                    </div>
                    <div class="card-footer">
                        <div class="form">
                            <div class="form-group from-show-notify row">
                                <div class="col-12 text-center">
                                    <button type="submit" id="submitBtn" class="btn btn-success">Update</button>
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
        var el = 0;

        $(document).ready(function() {
            // Language is now editable here too (was locked before). Sector /
            // Service / Status / Partners are all language-scoped FK rows, so
            // switching language must clear + reload them for the new one —
            // same cascade AJAX as create.blade.php's language handler, plus
            // a confirm (this WILL wipe the 4 dependent fields below) and a
            // revert-on-cancel so an accidental pick doesn't silently nuke
            // real data on an already-saved portfolio.
            var prevLanguageId = $("select[name='language_id']").val();

            $("select[name='language_id']").on('change', function() {
                var langid = $(this).val();
                if (langid === prevLanguageId) return;

                if (!confirm('Changing language will clear Sector, Status and Partners below — you\'ll need to re-pick them for the new language. Continue?')) {
                    $(this).val(prevLanguageId).trigger('change');
                    return;
                }
                prevLanguageId = langid;

                $("#sectors, #portfolioStatuses, #partnerIds").removeAttr('disabled');

                $.get("{{ url('/') }}/admin/portfolio/" + langid + "/get_sectors", function(data) {
                    let options = `<option value="" disabled selected>Ex: Energy</option>`;
                    for (let i = 0; i < data.length; i++) {
                        options += `<option value="${data[i].id}">${data[i].name}</option>`;
                    }
                    $("#sectors").html(options);
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

            $.get("{{ route('admin.portfolio.images', $portfolio->id) }}", function(data) {
                for (var i = 0; i < data.length; i++) {
                    $("#imgtable").append('<tr class="trdb" id="trdb' + data[i].id +
                        '"><td><div class="thumbnail"><img style="width:150px;" src="{{ asset('assets/front/img/portfolios/sliders/') }}/' +
                        data[i].image +
                        '" alt="Ad Image"></div></td><td><button type="button" class="btn btn-danger pull-right rmvbtndb" onclick="rmvdbimg(' +
                        data[i].id + ')"><i class="fa fa-times"></i></button></td></tr>');
                }
            });
        });

        function rmvdbimg(indb) {
            $(".request-loader").addClass("show");
            $.ajax({
                url: "{{ route('admin.portfolio.sliderrmv') }}",
                type: 'POST',
                data: { _token: "{{ csrf_token() }}", fileid: indb },
                success: function(data) {
                    $(".request-loader").removeClass("show");
                    $("#trdb" + indb).remove();
                }
            });
        }

        Dropzone.options.myDropzone = {
            acceptedFiles: '.png, .jpg, .jpeg',
            url: "{{ route('admin.portfolio.sliderstore') }}",
            success: function(file, response) {
                var removeButton = Dropzone.createElement("<button class='rmv-btn'><i class='fa fa-times'></i></button>");
                var _this = this;
                removeButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    _this.removeFile(file);
                    rmvimg(response.file_id);
                });
                file.previewElement.appendChild(removeButton);
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
        // picker UI from offering an invalid date in the first place. Also
        // applied once immediately below for a portfolio that already has
        // a Submission Date saved.
        function applySubmissionDateCap() {
            var d = $('#submissionDate').datepicker('getDate');
            $('#endDate').datepicker('setEndDate', d || false);
        }
        $('#submissionDate').on('changeDate change', applySubmissionDateCap);
        applySubmissionDateCap();

        // Aperçu — POSTs the current (possibly edited, unsaved) form fields.
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
