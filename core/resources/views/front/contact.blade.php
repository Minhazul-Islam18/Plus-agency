@extends("front.$version.layout")

@section('pagename')
- {{__('Contact Us')}}
@endsection

@section('meta-keywords', "$be->contact_meta_keywords")
@section('meta-description', "$be->contact_meta_description")

@section('breadcrumb-title', $bs->contact_title)
@section('breadcrumb-subtitle', $bs->contact_subtitle)
@section('breadcrumb-link', __('Contact Us'))

@if(!empty($bs->contact_breadcrumb_bg))
@section('breadcrumb-bg', asset('assets/front/img/'.$bs->contact_breadcrumb_bg))
@endif

@if(!empty($bs->contact_breadcrumb_overlay_color))
@section('breadcrumb-overlay-color', $bs->contact_breadcrumb_overlay_color)
@endif

@if(!empty($bs->contact_breadcrumb_overlay_opacity))
@section('breadcrumb-overlay-opacity', $bs->contact_breadcrumb_overlay_opacity)
@endif

@section('content')

@if ($be->theme_version == 'dark')
    @php
        $darkAddresses = explode(PHP_EOL, $bex->contact_addresses);
        $darkPhones = explode(',', $bex->contact_numbers);
        $darkMails = explode(',', $bex->contact_mails);
    @endphp
    <!--    dark contact page start   -->
    <div class="dark-contact-page">
        <div class="dark-info-strip">
            <div class="dark-info-card">
                <div class="dark-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l9-8 9 8M5 10v10h14V10" /></svg></div>
                <div class="label">{{ __('Address') }}</div>
                @foreach ($darkAddresses as $address)
                    @if (trim($address) !== '')
                        <p>{{ trim($address) }}</p>
                    @endif
                @endforeach
            </div>
            <div class="dark-info-card">
                <div class="dark-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z" /></svg></div>
                <div class="label">{{ __('Phone') }}</div>
                @foreach ($darkPhones as $phone)
                    @if (trim($phone) !== '')
                        <p><a href="tel:{{ preg_replace('/\s+/', '', trim($phone)) }}">{{ trim($phone) }}</a></p>
                    @endif
                @endforeach
            </div>
            <div class="dark-info-card">
                <div class="dark-info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z" /><path d="M22 6l-10 7L2 6" /></svg></div>
                <div class="label">{{ __('Email') }}</div>
                @foreach ($darkMails as $mail)
                    @if (trim($mail) !== '')
                        <p><a href="mailto:{{ trim($mail) }}">{{ trim($mail) }}</a></p>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="dark-contact-split">
            <div class="reveal-left">
                <span class="dark-bc-eyebrow dark-cf-eyebrow">{{ __('Contact Us') }}</span>
                <div class="dark-cf-head">
                    <h2>{{ convertUtf8($bs->contact_form_subtitle) }}</h2>
                </div>
                <form action="{{ route('front.sendmail') }}" method="POST">
                    @csrf
                    <div class="dark-cf-row">
                        <div class="dark-cf-field">
                            <label>{{ __('Name') }}</label>
                            <input name="name" type="text" placeholder="{{ __('Name') }}" required>
                            @if ($errors->has('name'))
                                <p class="text-danger mb-0">{{ $errors->first('name') }}</p>
                            @endif
                        </div>
                        <div class="dark-cf-field">
                            <label>{{ __('Email') }}</label>
                            <input name="email" type="email" placeholder="{{ __('Email') }}" required>
                            @if ($errors->has('email'))
                                <p class="text-danger mb-0">{{ $errors->first('email') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="dark-cf-field">
                        <label>{{ __('Subject') }}</label>
                        <input name="subject" type="text" placeholder="{{ __('Subject') }}" required>
                        @if ($errors->has('subject'))
                            <p class="text-danger mb-0">{{ $errors->first('subject') }}</p>
                        @endif
                    </div>
                    <div class="dark-cf-field">
                        <label>{{ __('Message') }}</label>
                        <textarea name="message" id="comment" cols="30" rows="10" placeholder="{{ __('Comment') }}" required></textarea>
                        @if ($errors->has('message'))
                            <p class="text-danger mb-0">{{ $errors->first('message') }}</p>
                        @endif
                    </div>
                    @if ($bs->is_recaptcha == 1)
                        <div class="dark-cf-field">
                            {!! NoCaptcha::renderJs() !!}
                            {!! NoCaptcha::display() !!}
                            @if ($errors->has('g-recaptcha-response'))
                                @php
                                    $errmsg = $errors->first('g-recaptcha-response');
                                @endphp
                                <p class="text-danger mb-0">{{ __("$errmsg") }}</p>
                            @endif
                        </div>
                    @endif

                    <button type="submit" class="dark-cf-submit">
                        {{ __('Submit') }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </button>
                </form>
            </div>

            <div class="dark-map-wrap reveal-right">
                <iframe src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q={{ $bex->latitude }},%20{{ $bex->longitude }}+(ICA)&amp;t=&amp;z={{ $bex->map_zoom }}&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
                <div class="dark-map-pin-card">
                    <div class="dark-map-pin-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" /><circle cx="12" cy="10" r="3" /></svg></div>
                    <div class="dark-map-pin-text">
                        @if (!empty($darkAddresses[0]))
                            <strong>{{ trim($darkAddresses[0]) }}</strong>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--    dark contact page end   -->
@else
    <!--    contact form and map start   -->
    <div class="contact-form-section">
        <div class="container">
            <div class="contact-infos mb-5">
                <div class="row no-gutters">
                    <div class="col-lg-4 single-info-col">
                        <div class="single-info wow fadeInRight" data-wow-duration="1s" style="visibility: visible; animation-duration: 1s; animation-name: fadeInRight;">
                            <div class="icon-wrapper"><i class="fas fa-home"></i></div>
                            <div class="info-txt">
                                @php
                                    $addresses = explode(PHP_EOL, $bex->contact_addresses);
                                @endphp
                                @foreach ($addresses as $address)
                                <p><i class="fas fa-map-pin base-color mr-1"></i> {{$address}}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 single-info-col">
                        <div class="single-info wow fadeInRight" data-wow-duration="1s" data-wow-delay=".2s" style="visibility: visible; animation-duration: 1s; animation-delay: 0.2s; animation-name: fadeInRight;">
                            <div class="icon-wrapper"><i class="fas fa-phone"></i></div>
                            <div class="info-txt">
                                @php
                                    $phones = explode(',', $bex->contact_numbers);
                                @endphp
                                @foreach ($phones as $phone)
                                <p>{{$phone}}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 single-info-col">
                        <div class="single-info wow fadeInRight" data-wow-duration="1s" data-wow-delay=".4s" style="visibility: visible; animation-duration: 1s; animation-delay: 0.4s; animation-name: fadeInRight;">
                            <div class="icon-wrapper"><i class="far fa-envelope"></i></div>
                            <div class="info-txt">
                                @php
                                    $mails = explode(',', $bex->contact_mails);
                                @endphp
                                @foreach ($mails as $mail)
                                <p><a href="mailto:{{trim($mail)}}">{{$mail}}</a></p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 reveal-left">
                    <span class="section-title">{{convertUtf8($bs->contact_form_title)}}</span>
                    <h2 class="section-summary">{{convertUtf8($bs->contact_form_subtitle)}}</h2>
                    <form action="{{route('front.sendmail')}}" class="contact-form" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-element">
                                    <input name="name" type="text" placeholder="{{__('Name')}}" required>
                                </div>
                                @if ($errors->has('name'))
                                <p class="text-danger mb-0">{{$errors->first('name')}}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="form-element">
                                    <input name="email" type="email" placeholder="{{__('Email')}}" required>
                                </div>
                                @if ($errors->has('email'))
                                <p class="text-danger mb-0">{{$errors->first('email')}}</p>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <div class="form-element">
                                    <input name="subject" type="text" placeholder="{{__('Subject')}}" required>
                                </div>
                                @if ($errors->has('subject'))
                                <p class="text-danger mb-0">{{$errors->first('subject')}}</p>
                                @endif
                            </div>
                            <div class="col-md-12">
                                <div class="form-element">
                                    <textarea name="message" id="comment" cols="30" rows="10" placeholder="{{__('Comment')}}" required></textarea>
                                </div>
                                @if ($errors->has('message'))
                                <p class="text-danger mb-0">{{$errors->first('message')}}</p>
                                @endif
                            </div>
                            @if ($bs->is_recaptcha == 1)
                            <div class="col-lg-12 mb-4">
                                {!! NoCaptcha::renderJs() !!}
                                {!! NoCaptcha::display() !!}
                                @if ($errors->has('g-recaptcha-response'))
                                @php
                                $errmsg = $errors->first('g-recaptcha-response');
                                @endphp
                                <p class="text-danger mb-0">{{__("$errmsg")}}</p>
                                @endif
                            </div>
                            @endif

                            <div class="col-md-12">
                                <div class="form-element no-margin">
                                    <input type="submit" value="{{__('Submit')}}">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-6 reveal-right">
                    <div class="map-wrapper">
                        <div id="map">
                            <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q={{$bex->latitude}},%20{{$bex->longitude}}+(My%20Business%20Name)&amp;t=&amp;z={{$bex->map_zoom}}&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--    contact form and map end   -->
@endif
@endsection
