@extends("front.$version.layout")

@section('pagename')
    - {{ convertUtf8($page->name) }}
@endsection

@section('meta-keywords', "$page->meta_keywords")
@section('meta-description', "$page->meta_description")

@section('breadcrumb-title', convertUtf8($page->title))
@section('breadcrumb-subtitle', convertUtf8($page->subtitle))
@section('breadcrumb-link', convertUtf8($page->name))

@if ($page->breadcrumb_image)
    @section('breadcrumb-bg', asset('assets/front/img/pages/' . $page->breadcrumb_image))
@endif

@if ($page->breadcrumb_overlay_color)
    @section('breadcrumb-overlay-color', $page->breadcrumb_overlay_color)
@endif

@if ($page->breadcrumb_overlay_opacity !== null)
    @section('breadcrumb-overlay-opacity', $page->breadcrumb_overlay_opacity)
@endif

@section('content')

@if ($be->theme_version == 'dark')
    <!--   dark custom page section start   -->
    <div class="dark-svcp-section">
        <div class="dark-svcp-inner dark-svcp-inner--full">
            <div>
                <div class="dark-svcd-panel">
                    <div class="dark-service-details">
                        {!! replaceBaseUrl($page->body) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--   dark custom page section end   -->
@else
    <!--   about company section start   -->
    <div class="about-company-section pt-115 pb-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    {!! replaceBaseUrl($page->body) !!}
                </div>
            </div>
        </div>
    </div>
    <!--   about company section end   -->
@endif
@endsection
