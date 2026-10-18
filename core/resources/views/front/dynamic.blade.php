@extends("front.$version.layout")

@section('pagename')
 - {{ !empty($page->meta_title) ? convertUtf8($page->meta_title) : convertUtf8($page->name) }}
@endsection

@section('meta-keywords', "$page->meta_keywords")
@section('meta-description', "$page->meta_description")
@if (!empty($page->og_image) || !empty($page->breadcrumb_image))
    @section('og-image', 'pages/' . (!empty($page->og_image) ? $page->og_image : $page->breadcrumb_image))
@endif

@section('breadcrumb-title', convertUtf8($page->title))
@section('breadcrumb-subtitle', convertUtf8($page->subtitle))
@section('breadcrumb-link', convertUtf8($page->name))

@if($page->breadcrumb_image)
@section('breadcrumb-bg', asset('assets/front/img/pages/' . $page->breadcrumb_image))
@endif

@if($page->breadcrumb_overlay_color)
@section('breadcrumb-overlay-color', $page->breadcrumb_overlay_color)
@endif

@if($page->breadcrumb_overlay_opacity !== null)
@section('breadcrumb-overlay-opacity', $page->breadcrumb_overlay_opacity)
@endif

@section('content')
    <style>
        {!! replaceBaseUrl($page->css) !!}
    </style>

    <div class="pagebuilder-content">
        {!! convertHtml($page->html) !!}
    </div>
@endsection
