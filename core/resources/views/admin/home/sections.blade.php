@extends('admin.layout')

@section('styles')
    <style>
        .sec-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        @media (max-width: 991px) {
            .sec-grid {
                grid-template-columns: 1fr;
            }
        }

        .sec-group-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #8d9498;
            margin: 32px 0 14px;
        }
        .sec-group-title:first-child {
            margin-top: 0;
        }

        .sec-card {
            background: #1a2035;
            border: 1px solid rgba(255, 255, 255, .06);
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .sec-card-head {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .sec-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: rgba(21, 114, 232, .12);
            color: #1572E8;
        }

        .sec-card-title {
            font-weight: 600;
            color: #fff;
            margin: 0;
        }

        .sec-card-sub {
            font-size: 11.5px;
            color: #8d9498;
            margin: 1px 0 0;
        }

        .sec-card-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-left: auto;
        }

        .sec-speed {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: #b9babf;
        }

        .sec-speed input {
            width: 90px;
        }
    </style>
@endsection

@section('content')
<div class="page-header">
    <h4 class="page-title">Section Customization</h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="{{route('admin.dashboard')}}">
                <i class="flaticon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Home Page</a>
        </li>
        <li class="separator">
            <i class="flaticon-right-arrow"></i>
        </li>
        <li class="nav-item">
            <a href="#">Section Customization</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <form action="{{route('admin.sections.update')}}" method="post">
                @csrf
                <div class="card-header">
                    <div class="card-title">Customize Sections</div>
                    <p class="text-muted mb-0" style="font-size:12.5px;">
                        Turn homepage sections on or off, and set how fast each carousel-driven section auto-rotates.
                    </p>
                </div>
                <div class="card-body pt-4 pb-4">

                    @php
                        $sec = function ($name, $icon, $label, $sub, $speedField = null) use ($abs, $abe) {
                            return compact('name', 'icon', 'label', 'sub', 'speedField', 'abs', 'abe');
                        };
                    @endphp

                    <div class="sec-group-title">Homepage Content</div>
                    <div class="sec-grid">
                        @include('admin.home.partials.section-toggle', $sec('feature_section', 'fa-star', 'Feature Section', 'Top highlight strip above the fold.'))
                        @include('admin.home.partials.section-toggle', $sec('intro_section', 'fa-info-circle', 'Introduction Section', 'Who-we-are intro block.'))
                        @include('admin.home.partials.section-toggle', $sec('service_section', 'fa-concierge-bell', 'Service Section', 'Services grid/list.'))
                        @include('admin.home.partials.section-toggle', $sec('approach_section', 'fa-route', 'Approach Section', 'How-we-work steps.', 'approach_carousel_speed'))
                        @include('admin.home.partials.section-toggle', $sec('statistics_section', 'fa-chart-bar', 'Statistics Section', 'Animated counters.'))
                        @include('admin.home.partials.section-toggle', $sec('portfolio_section', 'fa-briefcase', 'Portfolio Section', 'Case-studies carousel.', 'portfolio_carousel_speed'))
                        @include('admin.home.partials.section-toggle', $sec('tender_section', 'fa-file-contract', 'Tenders Section', 'Open tenders carousel.', 'tender_carousel_speed'))
                        @include('admin.home.partials.section-toggle', $sec('testimonial_section', 'fa-quote-right', 'Testimonial Section', 'Client testimonials carousel.', 'testimonial_carousel_speed'))
                        @include('admin.home.partials.section-toggle', $sec('team_section', 'fa-users', 'Team Section', 'Team members carousel.', 'team_carousel_speed'))
                        @include('admin.home.partials.section-toggle', $sec('call_to_action_section', 'fa-bullhorn', 'Call to Action Section', 'CTA banner.'))
                    </div>

                    <div class="sec-group-title">Site-wide</div>
                    <div class="sec-grid">
                        @include('admin.home.partials.section-toggle', $sec('news_section', 'fa-newspaper', 'News Section', 'Latest blog posts carousel.', 'blog_carousel_speed'))
                        @include('admin.home.partials.section-toggle', $sec('partner_section', 'fa-handshake', 'Partners Section', 'Partner logos carousel.', 'partner_carousel_speed'))
                        @include('admin.home.partials.section-toggle', $sec('top_footer_section', 'fa-layer-group', 'Top Footer Section', 'Footer widgets row.'))
                        @include('admin.home.partials.section-toggle', $sec('copyright_section', 'fa-copyright', 'Copyright Section', 'Bottom copyright bar.'))
                    </div>

                </div>
                <div class="card-footer">
                    <div class="form">
                        <div class="form-group from-show-notify row">
                            <div class="col-12 text-center">
                                <button type="submit" id="displayNotif" class="btn btn-success">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
