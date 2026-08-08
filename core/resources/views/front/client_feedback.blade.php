@extends("front.$version.layout")

@section('pagename')
  - {{__('Client Feedback')}}
@endsection

@section('styles')
  <link rel="stylesheet" href="{{ asset('assets/front/css/jquery-ui.min.css') }}">

  <link rel="stylesheet" href="{{ asset('assets/front/css/nice-select.css') }}">
@endsection

@section('breadcrumb-title', $bex->client_feedback_title)
@section('breadcrumb-subtitle', $bex->client_feedback_subtitle)
@section('breadcrumb-link', __('Client Feedback'))

@section('content')
@if ($be->theme_version == 'dark')
  <!--   dark feedback / evaluation sheet start   -->
  <section class="dark-feedback-section">
    <div class="dark-fb-sheet">
      <div class="dark-fb-left">
        <span class="dark-fb-kicker">{{ __('Evaluation Form') }}</span>
        <h2>{{ __('Your feedback shapes our practice') }}</h2>
        <p>{{ convertUtf8($bex->client_feedback_subtitle) }}</p>

        <div class="dark-scale-legend">
          <div class="dark-scale-legend-label">{{ __('Rating Scale') }}</div>
          <div class="dark-scale-legend-row" data-legend="1"><span class="n">01</span><span>{{ __('Unsatisfactory') }}</span></div>
          <div class="dark-scale-legend-row" data-legend="2"><span class="n">02</span><span>{{ __('Needs Improvement') }}</span></div>
          <div class="dark-scale-legend-row" data-legend="3"><span class="n">03</span><span>{{ __('Satisfactory') }}</span></div>
          <div class="dark-scale-legend-row" data-legend="4"><span class="n">04</span><span>{{ __('Very Satisfactory') }}</span></div>
          <div class="dark-scale-legend-row" data-legend="5"><span class="n">05</span><span>{{ __('Excellent') }}</span></div>
        </div>
      </div>

      <div class="dark-fb-right">
        <form action="{{ route('store_feedback') }}" method="POST">
          @csrf
          <input type="hidden" id="ratingId" name="rating" value="{{ old('rating') }}">

          <div class="dark-sheet-field" style="margin-bottom:0;">
            <label>{{ __('Overall Rating') }}</label>
            <div class="dark-score-picker" id="darkScorePicker">
              <button type="button" class="dark-score-dial" data-val="1"><svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6.3 6.9.7-5.2 4.7 1.6 6.8L12 17.8l-6.2 3.2 1.6-6.8-5.2-4.7 6.9-.7z"/></svg></button>
              <button type="button" class="dark-score-dial" data-val="2"><svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6.3 6.9.7-5.2 4.7 1.6 6.8L12 17.8l-6.2 3.2 1.6-6.8-5.2-4.7 6.9-.7z"/></svg></button>
              <button type="button" class="dark-score-dial" data-val="3"><svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6.3 6.9.7-5.2 4.7 1.6 6.8L12 17.8l-6.2 3.2 1.6-6.8-5.2-4.7 6.9-.7z"/></svg></button>
              <button type="button" class="dark-score-dial" data-val="4"><svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6.3 6.9.7-5.2 4.7 1.6 6.8L12 17.8l-6.2 3.2 1.6-6.8-5.2-4.7 6.9-.7z"/></svg></button>
              <button type="button" class="dark-score-dial" data-val="5"><svg viewBox="0 0 24 24"><path d="M12 2.5l2.9 6.3 6.9.7-5.2 4.7 1.6 6.8L12 17.8l-6.2 3.2 1.6-6.8-5.2-4.7 6.9-.7z"/></svg></button>
              <span class="dark-score-value"><b id="darkScoreValueNum">&mdash;</b>/5</span>
            </div>
            @if ($errors->has('rating'))
              <p class="text-danger mb-2 text-left">{{ $errors->first('rating') }}</p>
            @endif
          </div>

          <div class="dark-sheet-row">
            <div class="dark-sheet-field">
              <label>{{ __('Name') }}</label>
              <input type="text" placeholder="{{ __('Your name') }}" name="name" value="{{ old('name') }}">
              @if ($errors->has('name'))
                <p class="text-danger mb-2 text-left">{{ $errors->first('name') }}</p>
              @endif
            </div>
            <div class="dark-sheet-field">
              <label>{{ __('Email') }}</label>
              <input type="email" placeholder="you@example.com" name="email" value="{{ old('email') }}">
              @if ($errors->has('email'))
                <p class="text-danger mb-2 text-left">{{ $errors->first('email') }}</p>
              @endif
            </div>
          </div>

          <div class="dark-sheet-field">
            <label>{{ __('Subject') }}</label>
            <input type="text" placeholder="{{ __('Subject of your message') }}" name="subject" value="{{ old('subject') }}">
            @if ($errors->has('subject'))
              <p class="text-danger mb-2 text-left">{{ $errors->first('subject') }}</p>
            @endif
          </div>

          <div class="dark-sheet-field">
            <label>{{ __('Message') }}</label>
            <textarea placeholder="{{ __('Write Feedback') }}" name="feedback">{{ old('feedback') }}</textarea>
            @if ($errors->has('feedback'))
              <p class="text-danger mb-2 text-left">{{ $errors->first('feedback') }}</p>
            @endif
          </div>

          <div class="dark-sheet-submit-row">
            <span class="dark-sheet-submit-note">{{ __('Response within 48 business hours') }}</span>
            <button type="submit" class="dark-sheet-submit">
              {{ __('Submit') }}
              <span class="arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
  <!--   dark feedback / evaluation sheet end   -->
@else
  <section class="feedback-area-v1 pt-120 pb-120">
    <div class="container">
      <div class="row">

        <div class="col-lg-2"></div>
        <div class="col-lg-8">
          <div class="feedback-form">
            <form action="{{ route('store_feedback') }}" method="POST">
              @csrf
              <div class="row">
                <input type="hidden" id="ratingId" name="rating" value="{{ old('rating') }}">

                <div class="col-lg-12">
                  <div class="form_group">
                    <div class="rating-box">
                      <ul class="feedback-rating rating-1">
                        <li>
                          <a class="cursor-pointer" data-ratingVal="1"><i class="far fa-star"></i></a>
                        </li>
                      </ul>
                      <ul class="feedback-rating rating-2">
                        <li>
                          <a class="cursor-pointer" data-ratingVal="2"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="2"><i class="far fa-star"></i></a>
                        </li>
                      </ul>
                      <ul class="feedback-rating rating-3">
                        <li>
                          <a class="cursor-pointer" data-ratingVal="3"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="3"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="3"><i class="far fa-star"></i></a>
                        </li>
                      </ul>
                      <ul class="feedback-rating rating-4">
                        <li>
                          <a class="cursor-pointer" data-ratingVal="4"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="4"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="4"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="4"><i class="far fa-star"></i></a>
                        </li>
                      </ul>
                      <ul class="feedback-rating rating-5">
                        <li>
                          <a class="cursor-pointer" data-ratingVal="5"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="5"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="5"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="5"><i class="far fa-star"></i></a>
                        </li>
                        <li>
                          <a class="cursor-pointer" data-ratingVal="5"><i class="far fa-star"></i></a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  @if ($errors->has('rating'))
                    <p class="text-danger mb-2 text-left">{{ $errors->first('rating') }}</p>
                  @endif
                </div>

                <div class="col-lg-12">
                  <div class="form_group">
                    <input type="text" class="form_control" placeholder="{{__('Enter Name')}}" name="name" value="{{ old('name') }}">
                  </div>
                  @if ($errors->has('name'))
                    <p class="text-danger mb-2 text-left">{{ $errors->first('name') }}</p>
                  @endif
                </div>

                <div class="col-lg-12">
                  <div class="form_group">
                    <input type="email" class="form_control" placeholder="{{__('Email Address')}}" name="email" value="{{ old('email') }}">
                  </div>
                  @if ($errors->has('email'))
                    <p class="text-danger mb-2 text-left">{{ $errors->first('email') }}</p>
                  @endif
                </div>

                <div class="col-lg-12">
                  <div class="form_group">
                    <input type="text" class="form_control" placeholder="{{__('Subject')}}" name="subject" value="{{ old('subject') }}">
                  </div>
                  @if ($errors->has('subject'))
                    <p class="text-danger mb-2 text-left">{{ $errors->first('subject') }}</p>
                  @endif
                </div>

                <div class="col-lg-12">
                  <textarea class="form_control pt-4" placeholder="{{__('Write Feedback')}}" name="feedback">{{ old('feedback') }}</textarea>
                  @if ($errors->has('feedback'))
                    <p class="text-danger mb-2 text-left">{{ $errors->first('feedback') }}</p>
                  @endif
                </div>

                <div class="col-lg-12">
                  <div class="form_group text-center">
                    <button class="main-btn btn base-bg">{{ __('Submit') }}</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endif
@endsection

@section('scripts')
  <script src="{{ asset('assets/front/js/jquery.ui.js') }}"></script>

  <script src="{{ asset('assets/front/js/jquery.nice-select.min.js') }}"></script>

  <script>
    $(document).ready(function () {
      // jquery nice select js
      $('select').niceSelect();

      // re-highlight the star rating after a validation error redirect
      var oldRating = $('#ratingId').val();
      if (oldRating) {
        $('.rating-' + oldRating + ' li a i').addClass('text-success');
      }
    });

    // get the rating (star) value in integer
    $(document).on('click', '.feedback-rating li a', function() {
      let ratingValue = $(this).attr('data-ratingVal');

      // first, remove star color from all the 'feedback-rating' class
      $('.feedback-rating li a i').removeClass('text-success');

      // second, add star color to the selected parent class
      let parentClass = `rating-${ratingValue}`;
      $('.' + parentClass + ' li a i').addClass('text-success');

      $('#ratingId').val(ratingValue);
    });
  </script>

  @if ($be->theme_version == 'dark')
    <script>
      (function () {
        var picker = document.getElementById('darkScorePicker');
        if (!picker) return;
        var dials = Array.prototype.slice.call(picker.querySelectorAll('.dark-score-dial'));
        var valueNum = document.getElementById('darkScoreValueNum');
        var ratingInput = document.getElementById('ratingId');
        var currentScore = parseInt(ratingInput.value, 10) || 0;

        function paint(upto) {
          dials.forEach(function (d) {
            var v = parseInt(d.getAttribute('data-val'), 10);
            d.classList.toggle('is-filled', v <= upto);
          });
        }
        function syncLegend(val) {
          document.querySelectorAll('.dark-scale-legend-row').forEach(function (row) {
            row.classList.toggle('is-active', parseInt(row.getAttribute('data-legend'), 10) === val);
          });
        }

        if (currentScore) {
          paint(currentScore);
          syncLegend(currentScore);
          valueNum.textContent = currentScore;
        }

        dials.forEach(function (dial) {
          var val = parseInt(dial.getAttribute('data-val'), 10);
          dial.addEventListener('mouseenter', function () {
            picker.classList.add('has-hover');
            dials.forEach(function (d) {
              d.classList.toggle('is-preview', parseInt(d.getAttribute('data-val'), 10) <= val);
            });
          });
          dial.addEventListener('click', function () {
            currentScore = val;
            ratingInput.value = val;
            valueNum.textContent = val;
            paint(currentScore);
            syncLegend(currentScore);
          });
        });
        picker.addEventListener('mouseleave', function () {
          picker.classList.remove('has-hover');
          dials.forEach(function (d) { d.classList.remove('is-preview'); });
        });
      })();
    </script>
  @endif
@endsection
