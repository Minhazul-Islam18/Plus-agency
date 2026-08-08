@extends('admin.layout')

@if(!empty($abs->language) && $abs->language->rtl == 1)
@section('styles')
<style>
    form input,
    form textarea,
    form select,
    select {
        direction: rtl;
    }
</style>
@endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Dark Theme Hero</h4>
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
        <a href="#">Dark Theme Hero</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card-title">Update Dark Theme Hero</div>
                    <p class="text-muted mb-0">Only used when Theme & Home &rarr; Settings has Dark theme selected. The light theme's hero is unaffected.</p>
                </div>
                <div class="col-lg-2">
                    @if (!empty($langs))
                        <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                            <option value="" selected disabled>Select a Language</option>
                            @foreach ($langs as $lang)
                                <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body pt-5 pb-4">
          <div class="row">
            <div class="col-lg-6 offset-lg-3">

              <form id="ajaxForm" action="{{route('admin.darkhero.update', $lang_id)}}" method="post">
                @csrf

                <div class="form-group">
                    <label for="">Eyebrow Badge <small class="text-muted">(small label above the title)</small></label>
                    <input type="text" class="form-control" name="eyebrow" value="{{$abs->eyebrow}}" placeholder="e.g. Financial &amp; Strategic Advisory">
                    <p id="erreyebrow" class="em text-danger mb-0"></p>
                </div>

                <div class="form-group">
                    <label for="">Title</label>
                    <input type="text" class="form-control" name="title" value="{{$abs->title}}">
                    <p id="errtitle" class="em text-danger mb-0"></p>
                </div>

                <div class="form-group">
                    <label for="">Rotating Titles <small class="text-muted">(one per line — cycles with a flip animation after the Title above. Leave empty to show just the Title.)</small></label>
                    <textarea class="form-control" name="rotating_titles" rows="3" placeholder="e.g.&#10;Trusted Partner For Growth&#10;Smart Strategy For Wealth">{{$abs->rotating_titles}}</textarea>
                    <p id="errrotating_titles" class="em text-danger mb-0"></p>
                </div>

                <div class="form-group">
                    <label for="">Text</label>
                    <input type="text" class="form-control" name="text" value="{{$abs->text}}">
                    <p id="errtext" class="em text-danger mb-0"></p>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Button Text</label>
                            <input type="text" class="form-control" name="button_text" value="{{$abs->button_text}}">
                            <p id="errbutton_text" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Button URL</label>
                            <input type="text" class="form-control ltr" name="button_url" value="{{$abs->button_url}}">
                            <p id="errbutton_url" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Meta Line — Left <small class="text-muted">(small detail shown under the button, e.g. a location)</small></label>
                            <input type="text" class="form-control" name="meta_left" value="{{$abs->meta_left}}" placeholder="e.g. Ouagadougou, Burkina Faso">
                            <p id="errmeta_left" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label for="">Meta Line — Right <small class="text-muted">(e.g. an established year)</small></label>
                            <input type="text" class="form-control" name="meta_right" value="{{$abs->meta_right}}" placeholder="e.g. Est. 2019">
                            <p id="errmeta_right" class="em text-danger mb-0"></p>
                        </div>
                    </div>
                </div>
              </form>

            </div>
          </div>
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
      </div>
    </div>
  </div>

@endsection
