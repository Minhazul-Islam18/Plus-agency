@foreach ($apopups as $popup)
    @continue($popup->status != 1)
    @php
        $type = $popup->type;
        $isDark = $be->theme_version == 'dark';
    @endphp
    @if ($type == 1)
        <div data-popup_delay="{{$popup->delay}}" data-popup_id="{{$popup->id}}" id="modal-popup{{$popup->id}}"
            class="popup-wrapper @if ($isDark) dark-popup @endif">
            <div class="@if ($isDark) dark-popup-box dark-popup-img-only @endif">
                @if ($isDark)
                    <button type="button" class="dark-popup-close" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                @endif
                <img class="lazy" data-src="{{asset('assets/front/img/popups/' . $popup->image)}}" alt="Popup Image" width="100%">
            </div>
        </div>
    @elseif ($type == 2)
        <div data-popup_delay="{{$popup->delay}}" data-popup_id="{{$popup->id}}" id="modal-popup{{$popup->id}}"
            class="popup-wrapper @if ($isDark) dark-popup @endif">
            <div class="popup-one bg_cover lazy @if ($isDark) dark-popup-box @endif" data-bg="{{asset('assets/front/img/popups/' . $popup->background_image)}}">
                @if ($isDark)
                    <button type="button" class="dark-popup-close" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                @endif
                <div class="popup_main-content">
                    @if ($isDark)<span class="dark-popup-eyebrow">{{ __('Announcement') }}</span>@endif
                    <h1>{{$popup->title}}</h1>
                    <p>{{$popup->text}}</p>

                    @if (!empty($popup->button_url) && !empty($popup->button_text))
                    <a href="{{$popup->button_url}}" class="popup-main-btn" style="background-color: #{{$popup->button_color}};">{{$popup->button_text}}</a>
                    @endif
                </div>
            </div>
        </div>
    @elseif ($type == 3)
        <div data-popup_delay="{{$popup->delay}}" data-popup_id="{{$popup->id}}" id="modal-popup{{$popup->id}}"
            class="popup-wrapper @if ($isDark) dark-popup @endif">
            <div class="popup-two bg_cover lazy @if ($isDark) dark-popup-box @endif" data-bg="{{asset('assets/front/img/popups/' . $popup->background_image)}}">
                @if ($isDark)
                    <button type="button" class="dark-popup-close" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                @endif
                <div class="popup_main-content">
                    @if ($isDark)<span class="dark-popup-eyebrow">{{ __('Newsletter') }}</span>@endif
                    <h1>{{$popup->title}}</h1>
                    <p>{{$popup->text}}</p>
                    <div class="subscribe-form">
                        <form id="subscribeForm" action="{{route('front.subscribe')}}" method="POST">
                            @csrf
                            <div class="form_group">
                                <input type="email" class="form_control" placeholder="{{__('Enter Email Address')}}" name="email" required>
                                <p id="erremail" class="text-white mb-3 err-email"></p>
                            </div>
                            <div class="form_group">
                                <button type="submit" class="popup-main-btn" style="background-color: #{{$popup->button_color}};">{{$popup->button_text}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($type == 4)
        <div data-popup_delay="{{$popup->delay}}" data-popup_id="{{$popup->id}}" id="modal-popup{{$popup->id}}"
            class="popup-wrapper @if ($isDark) dark-popup @endif">
            <div class="popup-three @if ($isDark) dark-popup-box @endif">
                @if ($isDark)
                    <button type="button" class="dark-popup-close" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                @endif
                <div class="popup_main-content">
                    <div class="left-bg bg_cover lazy" data-bg="{{asset('assets/front/img/popups/' . $popup->image)}}"></div>
                    <div class="right-content">
                        @if ($isDark)<span class="dark-popup-eyebrow">{{ __('Announcement') }}</span>@endif
                        <h1>{{$popup->title}}</h1>
                        <p>{{$popup->text}}</p>

                        @if (!empty($popup->button_url) && !empty($popup->button_text))
                        <a href="{{$popup->button_url}}" class="popup-main-btn" style="background-color: #{{$popup->button_color}};">{{$popup->button_text}}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif ($type == 5)
        <div data-popup_delay="{{$popup->delay}}" data-popup_id="{{$popup->id}}" id="modal-popup{{$popup->id}}"
            class="popup-wrapper @if ($isDark) dark-popup @endif">
            <div class="popup-four @if ($isDark) dark-popup-box @endif">
                @if ($isDark)
                    <button type="button" class="dark-popup-close" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                @endif
                <div class="popup_main-content">
                    <div class="left-bg bg_cover lazy" data-bg="{{asset('assets/front/img/popups/' . $popup->image)}}"></div>
                    <div class="right-content">
                        @if ($isDark)<span class="dark-popup-eyebrow">{{ __('Newsletter') }}</span>@endif
                        <h1>{{$popup->title}}</h1>
                        <p>{{$popup->text}}</p>
                        <div class="subscribe-form">
                            <form id="subscribeForm" action="{{route('front.subscribe')}}" method="POST">
                                @csrf
                                <div class="form_group">
                                    <input type="email" class="form_control" placeholder="{{__('Enter Email Address')}}" name="email" required>
                                    <p id="erremail" class="text-danger mb-3 err-email"></p>
                                </div>
                                <div class="form_group">
                                    <button type="submit" class="popup-main-btn" style="background-color: #{{$popup->button_color}};">{{$popup->button_text}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($type == 6)
        <div data-popup_delay="{{$popup->delay}}" data-popup_id="{{$popup->id}}" id="modal-popup{{$popup->id}}"
            class="popup-wrapper @if ($isDark) dark-popup @endif">
            <div class="popup-five bg_cover lazy @if ($isDark) dark-popup-box @endif" data-bg="{{asset('assets/front/img/popups/' . $popup->background_image)}}">
                @if ($isDark)
                    <button type="button" class="dark-popup-close" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                @endif
                <div class="popup_main-content">
                    @if ($isDark)<span class="dark-popup-eyebrow">{{ __('Live Opportunity') }}</span>@endif
                    <h1>{{$popup->title}}</h1>
                    <h4>{{$popup->text}}</h4>
                    <div class="offer-timer" data-end_date="{!! $popup->end_date !!}" data-end_time="{!! $popup->end_time !!}"></div>
                    @if (!empty($popup->button_url) && !empty($popup->button_text))
                        <a href="{{$popup->button_url}}" class="popup-main-btn" style="background-color: #{{$popup->button_color}};">{{$popup->button_text}}</a>
                    @endif
                </div>
            </div>
        </div>
    @elseif ($type == 7)
        <div data-popup_delay="{{$popup->delay}}" data-popup_id="{{$popup->id}}" id="modal-popup{{$popup->id}}"
            class="popup-wrapper @if ($isDark) dark-popup @endif">
            <div class="popup-six @if ($isDark) dark-popup-box @endif">
                @if ($isDark)
                    <button type="button" class="dark-popup-close" aria-label="{{ __('Close') }}">
                        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                @endif
                <div class="popup_main-content">
                    <div class="left-bg bg_cover lazy" data-bg="{{asset('assets/front/img/popups/' . $popup->image)}}"></div>
                    <div class="right-content bg_cover" style="background-color: #{{$popup->background_color}}; background-image: url({{asset('assets/front/img/popup-7-bg.png')}});">
                        @if ($isDark)
                            <span class="dark-popup-eyebrow">{{ __('Live Opportunity') }}</span>
                        @endif
                        <h1>{{$popup->title}}</h1>
                        <h4>{{$popup->text}}</h4>
                        <div class="offer-timer" data-end_date="{!! $popup->end_date !!}" data-end_time="{!! $popup->end_time !!}"></div>
                        @if (!empty($popup->button_url) && !empty($popup->button_text))
                            <a href="{{$popup->button_url}}" class="popup-main-btn" style="background-color: #{{$popup->button_color}};">{{$popup->button_text}}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach
