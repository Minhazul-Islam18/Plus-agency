<li
    class="nav-item
@if (request()->path() == config('app.admin_prefix','admin').'/features') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/introsection') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/servicesection') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/static') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/darkhero') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/video') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/sliders') active
@elseif(request()->is(config('app.admin_prefix','admin').'/herosection/slider/*/edit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/approach') active
@elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/statistics') active
@elseif(request()->is(config('app.admin_prefix','admin').'/statistics/*/edit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/members') active
@elseif(request()->is(config('app.admin_prefix','admin').'/member/*/edit')) active
@elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/cta') active
@elseif(request()->is(config('app.admin_prefix','admin').'/feature/*/edit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/testimonials') active
@elseif(request()->is(config('app.admin_prefix','admin').'/testimonial/*/edit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/invitation') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/partners') active
@elseif(request()->is(config('app.admin_prefix','admin').'/partner/*/edit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/portfoliosection') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/tendersection') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/blogsection') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/member/create') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/sections') active

@elseif(request()->path() == config('app.admin_prefix','admin').'/scategorys') active
@elseif(request()->is(config('app.admin_prefix','admin').'/service/settings')) active
@elseif(request()->is(config('app.admin_prefix','admin').'/scategory/*/edit')) active
@elseif(request()->path() == config('app.admin_prefix','admin').'/services') active
@elseif(request()->is(config('app.admin_prefix','admin').'/service/*/edit')) active


@elseif(request()->path() == config('app.admin_prefix','admin').'/portfolios') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/portfolio/create') active
@elseif(request()->is(config('app.admin_prefix','admin').'/portfolio/*/edit')) active

@elseif(request()->path() == config('app.admin_prefix','admin').'/blog/settings') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/bcategorys') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/blogs') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/archives') active
@elseif(request()->is(config('app.admin_prefix','admin').'/blog/*/edit')) active

@elseif(request()->path() == config('app.admin_prefix','admin').'/footers') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/ulinks') active

@elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/settings') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/categories') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/gallery') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/create') active
@elseif(request()->is(config('app.admin_prefix','admin').'/gallery/*/edit')) active

@elseif(request()->path() == config('app.admin_prefix','admin').'/faq/settings') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/faq/categories') active
@elseif(request()->path() == config('app.admin_prefix','admin').'/faqs') active

@elseif(request()->path() == config('app.admin_prefix','admin').'/contact') active @endif">
    <a data-toggle="collapse" href="#webContents">
        <i class="la flaticon-imac"></i>
        <p>Content Management</p>
        <span class="caret"></span>
    </a>
    <div class="collapse
    @if (request()->path() == config('app.admin_prefix','admin').'/features') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/introsection') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/servicesection') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/static') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/darkhero') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/video') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/sliders') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/herosection/slider/*/edit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/approach') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/statistics') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/statistics/*/edit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/members') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/member/*/edit')) show
    @elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/cta') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/feature/*/edit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/testimonials') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/testimonial/*/edit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/invitation') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/partners') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/partner/*/edit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/portfoliosection') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/tendersection') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/blogsection') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/member/create') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/sections') show

    @elseif(request()->path() == config('app.admin_prefix','admin').'/scategorys') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/service/settings')) show
    @elseif(request()->is(config('app.admin_prefix','admin').'/scategory/*/edit')) show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/services') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/service/*/edit')) show


    @elseif(request()->path() == config('app.admin_prefix','admin').'/portfolios') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/portfolio/create') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/portfolio/*/edit')) show

    @elseif(request()->path() == config('app.admin_prefix','admin').'/blog/settings') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/bcategorys') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/blogs') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/archives') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/blog/*/edit')) show

    @elseif(request()->path() == config('app.admin_prefix','admin').'/footers') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/ulinks') show

    @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/settings') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/categories') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/create') show
    @elseif(request()->is(config('app.admin_prefix','admin').'/gallery/*/edit')) show

    @elseif(request()->path() == config('app.admin_prefix','admin').'/faq/settings') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/faq/categories') show
    @elseif(request()->path() == config('app.admin_prefix','admin').'/faqs') show

    @elseif(request()->path() == config('app.admin_prefix','admin').'/contact') show @endif"
        id="webContents">
        <ul class="nav nav-collapse">

            {{-- Home Page Sections --}}
            <li
                class="
            @if (request()->path() == config('app.admin_prefix','admin').'/features') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/introsection') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/servicesection') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/static') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/darkhero') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/video') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/sliders') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/herosection/slider/*/edit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/approach') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/statistics') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/statistics/*/edit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/members') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/member/*/edit')) selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/cta') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/feature/*/edit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/testimonials') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/testimonial/*/edit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/invitation') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/partners') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/partner/*/edit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/portfoliosection') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/tendersection') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/blogsection') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/member/create') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/sections') selected @endif">
                <a data-toggle="collapse" href="#home">
                    <span class="sub-item">Home Page Sections</span>
                    <span class="caret"></span>
                </a>
                <div class="collapse
                @if (request()->path() == config('app.admin_prefix','admin').'/features') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/introsection') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/servicesection') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/static') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/darkhero') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/video') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/sliders') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/herosection/slider/*/edit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/approach') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/statistics') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/statistics/*/edit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/members') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/member/*/edit')) show
                @elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/cta') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/feature/*/edit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/testimonials') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/testimonial/*/edit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/invitation') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/partners') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/partner/*/edit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/portfoliosection') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/tendersection') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/blogsection') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/member/create') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/sections') show @endif"
                    id="home">
                    <ul class="nav nav-collapse subnav">
                        <li
                            class="
                        @if (request()->path() == config('app.admin_prefix','admin').'/herosection/static') selected
                        @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/video') selected
                        @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/sliders') selected
                        @elseif(request()->path() == config('app.admin_prefix','admin').'/darkhero') selected
                        @elseif(request()->is(config('app.admin_prefix','admin').'/herosection/slider/*/edit')) selected @endif">
                            <a data-toggle="collapse" href="#herosection">
                                <span class="sub-item">Hero Section</span>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse
                        @if (request()->path() == config('app.admin_prefix','admin').'/herosection/static') show
                        @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/video') show
                        @elseif(request()->path() == config('app.admin_prefix','admin').'/herosection/sliders') show
                        @elseif(request()->path() == config('app.admin_prefix','admin').'/darkhero') show
                        @elseif(request()->is(config('app.admin_prefix','admin').'/herosection/slider/*/edit')) show @endif"
                                id="herosection">
                                <ul class="nav nav-collapse subnav">
                                    {{-- Static & Video are light-theme-only variants. Dark theme's
                                         own "Static" is the signature-hero settings page (separate
                                         DarkHeroSetting model) rather than herosection.static. Slider
                                         is shared between both themes — same model/admin page either
                                         way — so it always shows regardless of theme. --}}
                                    @if ($be->theme_version == 'dark')
                                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/darkhero') active @endif">
                                            <a href="{{ route('admin.darkhero.index') . '?language=' . $default->code }}">
                                                <span class="sub-item">Static Version</span>
                                            </a>
                                        </li>
                                    @else
                                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/herosection/static') active @endif">
                                            <a
                                                href="{{ route('admin.herosection.static') . '?language=' . $default->code }}">
                                                <span class="sub-item">Static Version</span>
                                            </a>
                                        </li>
                                    @endif
                                    <li
                                        class="
                            @if (request()->path() == config('app.admin_prefix','admin').'/herosection/sliders') active
                            @elseif(request()->is(config('app.admin_prefix','admin').'/herosection/slider/*/edit')) active @endif">
                                        <a href="{{ route('admin.slider.index') . '?language=' . $default->code }}">
                                            <span class="sub-item">Slider Version</span>
                                        </a>
                                    </li>
                                    @if ($be->theme_version != 'dark')
                                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/herosection/video') active @endif">
                                            <a
                                                href="{{ route('admin.herosection.video') . '?language=' . $default->code }}">
                                                <span class="sub-item">Video Version</span>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        <li
                            class="
                    @if (request()->path() == config('app.admin_prefix','admin').'/features') active
                    @elseif(request()->is(config('app.admin_prefix','admin').'/feature/*/edit')) active @endif">
                            <a href="{{ route('admin.feature.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Features</span>
                            </a>
                        </li>

                        @if ($bex?->home_page_pagebuilder == 0)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/introsection') active @endif">
                                <a href="{{ route('admin.introsection.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Intro Section</span>
                                </a>
                            </li>
                        @endif

                        @if ($bex?->home_page_pagebuilder == 0)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/servicesection') active @endif">
                                <a href="{{ route('admin.servicesection.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Service Section</span>
                                </a>
                            </li>
                        @endif

                        <li
                            class="
                    @if (request()->path() == config('app.admin_prefix','admin').'/approach') active
                    @elseif(request()->is(config('app.admin_prefix','admin').'/approach/*/pointedit')) active @endif">
                            <a href="{{ route('admin.approach.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Approach Section</span>
                            </a>
                        </li>
                        <li
                            class="
                    @if (request()->path() == config('app.admin_prefix','admin').'/statistics') active
                    @elseif(request()->is(config('app.admin_prefix','admin').'/statistics/*/edit')) active @endif">
                            <a href="{{ route('admin.statistics.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Statistics Section</span>
                            </a>
                        </li>

                        @if ($bex?->home_page_pagebuilder == 0)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/cta') active @endif">
                                <a href="{{ route('admin.cta.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Call to Action Section</span>
                                </a>
                            </li>
                        @endif

                        @if ($bex?->home_page_pagebuilder == 0)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/portfoliosection') active @endif">
                                <a href="{{ route('admin.portfoliosection.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Portfolio Section</span>
                                </a>
                            </li>
                        @endif
                        @if ($bex?->home_page_pagebuilder == 0)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/tendersection') active @endif">
                                <a href="{{ route('admin.tendersection.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Tenders Section</span>
                                </a>
                            </li>
                        @endif
                        <li
                            class="
                    @if (request()->path() == config('app.admin_prefix','admin').'/testimonials') active
                    @elseif(request()->is(config('app.admin_prefix','admin').'/testimonial/*/edit')) active @endif">
                            <a href="{{ route('admin.testimonial.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Testimonials</span>
                            </a>
                        </li>
                        <li
                            class="
                    @if (request()->path() == config('app.admin_prefix','admin').'/members') active
                    @elseif(request()->is(config('app.admin_prefix','admin').'/member/*/edit')) active
                    @elseif(request()->path() == config('app.admin_prefix','admin').'/member/create') active @endif">
                            <a href="{{ route('admin.member.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Team Section</span>
                            </a>
                        </li>

                        @if ($bex?->home_page_pagebuilder == 0)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/blogsection') active @endif">
                                <a href="{{ route('admin.blogsection.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Blog Section</span>
                                </a>
                            </li>
                        @endif

                        <li
                            class="
                    @if (request()->path() == config('app.admin_prefix','admin').'/partners') active
                    @elseif(request()->is(config('app.admin_prefix','admin').'/partner/*/edit')) active @endif">
                            <a href="{{ route('admin.partner.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Partners</span>
                            </a>
                        </li>

                        @if ($bex?->home_page_pagebuilder == 0)
                            <li class="
                    @if (request()->path() == config('app.admin_prefix','admin').'/sections') active @endif">
                                <a href="{{ route('admin.sections.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Section Customization</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>
            </li>


            {{-- Footer --}}
            <li
                class="
            @if (request()->path() == config('app.admin_prefix','admin').'/footers') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/ulinks') selected @endif">
                <a data-toggle="collapse" href="#footer">
                    <span class="sub-item">Footer</span>
                    <span class="caret"></span>
                </a>
                <div class="collapse
                @if (request()->path() == config('app.admin_prefix','admin').'/footers') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/ulinks') show @endif"
                    id="footer">
                    <ul class="nav nav-collapse subnav">
                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/footers') active @endif">
                            <a href="{{ route('admin.footer.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Logo & Text</span>
                            </a>
                        </li>
                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/ulinks') active @endif">
                            <a href="{{ route('admin.ulink.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Useful Links</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Service Management --}}
            <li
                class="
            @if (request()->path() == config('app.admin_prefix','admin').'/scategorys') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/service/settings')) selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/scategory/*/edit')) selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/services') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/service/*/edit')) selected @endif">
                <a data-toggle="collapse" href="#service">
                    <span class="sub-item">Services</span>
                    <span class="caret"></span>
                </a>
                <div class="collapse
                @if (request()->path() == config('app.admin_prefix','admin').'/scategorys') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/service/settings')) show
                @elseif(request()->is(config('app.admin_prefix','admin').'/scategory/*/edit')) show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/services') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/service/*/edit')) show @endif"
                    id="service">
                    <ul class="nav nav-collapse subnav">
                        <li class="
                            @if (request()->path() == config('app.admin_prefix','admin').'/service/settings') active @endif">
                            <a href="{{ route('admin.service.settings') . '?language=' . $default->code }}">
                                <span class="sub-item">Settings</span>
                            </a>
                        </li>
                        @if (serviceCategory())
                            <li
                                class="
                        @if (request()->path() == config('app.admin_prefix','admin').'/scategorys') active
                        @elseif(request()->is(config('app.admin_prefix','admin').'/scategory/*/edit')) active @endif">
                                <a href="{{ route('admin.scategory.index') . '?language=' . $default->code }}">
                                    <span class="sub-item">Category</span>
                                </a>
                            </li>
                        @endif
                        <li
                            class="
                        @if (request()->path() == config('app.admin_prefix','admin').'/services') active
                        @elseif(request()->is(config('app.admin_prefix','admin').'/service/*/edit')) active @endif">
                            <a href="{{ route('admin.service.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Services</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>


            {{-- Portfolio Management --}}
            @php
                $portfolioActive = request()->is(
                    'admin/portfolios',
                    'admin/portfolio/create',
                    'admin/portfolio/*/edit',
                    'admin/portfolio/settings',
                );
            @endphp

            <li class="{{ $portfolioActive ? 'selected' : '' }}">
                <a data-toggle="collapse" href="#portfolio">
                    <span class="sub-item">Portfolios</span>
                    <span class="caret"></span>
                </a>

                <div class="collapse {{ $portfolioActive ? 'show' : '' }}" id="portfolio">
                    <ul class="nav nav-collapse subnav">

                        <li class="{{ request()->is(config('app.admin_prefix','admin').'/portfolio/settings') ? 'active' : '' }}">
                            <a href="{{ route('admin.portfolio.settings', ['language' => $default->code]) }}">
                                <span class="sub-item">Settings</span>
                            </a>
                        </li>

                        <li class="{{ request()->is(config('app.admin_prefix','admin').'/portfolio/create') ? 'active' : '' }}">
                            <a href="{{ route('admin.portfolio.create') }}">
                                <span class="sub-item">Add Portfolio</span>
                            </a>
                        </li>

                        <li class="{{ request()->is(config('app.admin_prefix','admin').'/portfolios', 'admin/portfolio/*/edit') ? 'active' : '' }}">
                            <a href="{{ route('admin.portfolio.index', ['language' => $default->code]) }}">
                                <span class="sub-item">Portfolios</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>

            {{-- Blogs Management --}}
            @php
                $blogActive = request()->is(
                    'admin/blog/settings',
                    'admin/bcategorys',
                    'admin/blogs',
                    'admin/archives',
                    'admin/blog/*/edit',
                );
            @endphp

            <li class="{{ $blogActive ? 'selected' : '' }}">
                <a data-toggle="collapse" href="#blogs">
                    <span class="sub-item">Blogs</span>
                    <span class="caret"></span>
                </a>

                <div class="collapse {{ $blogActive ? 'show' : '' }}" id="blogs">
                    <ul class="nav nav-collapse subnav">

                        <li class="{{ request()->is(config('app.admin_prefix','admin').'/blog/settings') ? 'active' : '' }}">
                            <a href="{{ route('admin.blog.settings', ['language' => $default->code]) }}">
                                <span class="sub-item">Settings</span>
                            </a>
                        </li>

                        <li class="{{ request()->is(config('app.admin_prefix','admin').'/bcategorys') ? 'active' : '' }}">
                            <a href="{{ route('admin.bcategory.index', ['language' => $default->code]) }}">
                                <span class="sub-item">Category</span>
                            </a>
                        </li>

                        <li class="{{ request()->is(config('app.admin_prefix','admin').'/blogs', 'admin/blog/*/edit') ? 'active' : '' }}">
                            <a href="{{ route('admin.blog.index', ['language' => $default->code]) }}">
                                <span class="sub-item">Blogs</span>
                            </a>
                        </li>

                        <li class="{{ request()->is(config('app.admin_prefix','admin').'/archives') ? 'active' : '' }}">
                            <a href="{{ route('admin.archive.index') }}">
                                <span class="sub-item">Archives</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>


            {{-- Gallery Management --}}
            <li
                class="
            @if (request()->path() == config('app.admin_prefix','admin').'/gallery/settings') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/categories') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/create') selected
            @elseif(request()->is(config('app.admin_prefix','admin').'/gallery/*/edit')) selected @endif">
                <a data-toggle="collapse" href="#gallery">
                    <span class="sub-item">Gallery</span>
                    <span class="caret"></span>
                </a>
                <div class="collapse
                @if (request()->path() == config('app.admin_prefix','admin').'/gallery/settings') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/categories') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/create') show
                @elseif(request()->is(config('app.admin_prefix','admin').'/gallery/*/edit')) show @endif"
                    id="gallery">
                    <ul class="nav nav-collapse subnav">
                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/gallery/settings') active @endif">
                            <a href="{{ route('admin.gallery.settings') . '?language=' . $default->code }}">
                                <span class="sub-item">Settings</span>
                            </a>
                        </li>
                        @if ($data->gallery_category_status == 1)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/gallery/categories') active @endif">
                                <a href="{{ route('admin.gallery.categories') . '?language=' . $default->code }}">
                                    <span class="sub-item">Categories</span>
                                </a>
                            </li>
                        @endif
                        <li
                            class="@if (request()->path() == config('app.admin_prefix','admin').'/gallery') active
                            @elseif(request()->path() == config('app.admin_prefix','admin').'/gallery/create') active
                            @elseif(request()->is(config('app.admin_prefix','admin').'/gallery/*/edit')) active @endif">
                            <a href="{{ route('admin.gallery.index') . '?language=' . $default->code }}">
                                <span class="sub-item">Gallery</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>


            {{-- FAQ Management --}}
            <li
                class="
            @if (request()->path() == config('app.admin_prefix','admin').'/faq/settings') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/faq/categories') selected
            @elseif(request()->path() == config('app.admin_prefix','admin').'/faqs') selected @endif">
                <a data-toggle="collapse" href="#faq">
                    <span class="sub-item">FAQ</span>
                    <span class="caret"></span>
                </a>
                <div class="collapse
                @if (request()->path() == config('app.admin_prefix','admin').'/faq/settings') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/faq/categories') show
                @elseif(request()->path() == config('app.admin_prefix','admin').'/faqs') show @endif"
                    id="faq">
                    <ul class="nav nav-collapse subnav">
                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/faq/settings') active @endif">
                            <a href="{{ route('admin.faq.settings') . '?language=' . $default->code }}">
                                <span class="sub-item">Settings</span>
                            </a>
                        </li>
                        @if ($data->faq_category_status == 1)
                            <li class="@if (request()->path() == config('app.admin_prefix','admin').'/faq/categories') active @endif">
                                <a href="{{ route('admin.faq.categories') . '?language=' . $default->code }}">
                                    <span class="sub-item">Categories</span>
                                </a>
                            </li>
                        @endif
                        <li class="@if (request()->path() == config('app.admin_prefix','admin').'/faqs') active @endif">
                            <a href="{{ route('admin.faq.index') . '?language=' . $default->code }}">
                                <span class="sub-item">FAQs</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>


            {{-- Contact Page --}}
            <li class="
            @if (request()->path() == config('app.admin_prefix','admin').'/contact') active @endif">
                <a href="{{ route('admin.contact.index') . '?language=' . $default->code }}">
                    <span class="sub-item">Contact Page</span>
                </a>
            </li>

        </ul>
    </div>

</li>
