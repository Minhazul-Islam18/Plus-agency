@extends('admin.layout')

@if(!empty($blog->language) && $blog->language->rtl == 1)
@section('styles')
<style>
    form input,
    form textarea,
    form select {
        direction: rtl;
    }
    form .note-editor.note-frame .note-editing-area .note-editable {
        direction: rtl;
        text-align: right;
    }
</style>
@endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">Edit Blog</h4>
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
        <a href="#">Blog Page</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Edit Blog</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">Edit Blog</div>
          <a class="btn btn-info btn-sm float-right d-inline-block" href="{{route('admin.blog.index') . '?language=' . request()->input('language')}}">
            <span class="btn-label">
              <i class="fas fa-backward" style="font-size: 12px;"></i>
            </span>
            Back
          </a>
        </div>
        <div class="card-body pt-5 pb-5">
          <div class="row">
            <div class="col-lg-6 offset-lg-3">

              <form id="ajaxForm" class="" action="{{route('admin.blog.update')}}" method="post">
                @csrf
                <input type="hidden" name="blog_id" value="{{$blog->id}}">

                {{-- Image Part --}}
                <div class="form-group">
                    <label for="">Image ** </label>
                    <br>
                    <div class="thumb-preview" id="thumbPreview1">
                        <img src="{{asset('assets/front/img/blogs/'.$blog->main_image)}}" alt="User Image">
                    </div>
                    <br>
                    <br>


                    <input id="fileInput1" type="hidden" name="image">
                    <button id="chooseImage1" class="choose-image btn btn-primary" type="button" data-multiple="false" data-toggle="modal" data-target="#lfmModal1">Choose Image</button>


                    <p class="text-warning mb-0">{{ allowed_image_extensions_label() }} images are allowed</p>
                    <p class="text-warning mb-0"><small>Recommended size: 1200x750px (16:10 landscape). The image is cropped to fit this ratio.</small></p>
                    <p class="em text-danger mb-0" id="errimage"></p>

                    <!-- Image LFM Modal -->
                    <div class="modal fade lfm-modal" id="lfmModal1" tabindex="-1" role="dialog" aria-labelledby="lfmModalTitle" aria-hidden="true">
                        <i class="fas fa-times-circle"></i>
                        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-body p-0">
                                    <iframe src="{{url('laravel-filemanager')}}?serial=1" style="width: 100%; height: 500px; overflow: hidden; border: none;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                  <label for="">Title **</label>
                  <input type="text" class="form-control" name="title" value="{{$blog->title}}" placeholder="Enter title">
                  <p id="errtitle" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label for="">URL Slug</label>
                  <div class="slug-input-group">
                    <input id="slugInput" type="text" class="form-control ltr" name="slug" value="{{ $blog->slug }}" placeholder="url-slug">
                    <button type="button" id="regenerateSlugBtn" class="slug-regen-btn" title="Rebuild a short, meaningful slug from the current title">
                      <i class="fas fa-sync-alt"></i> Regenerate
                    </button>
                  </div>
                  <p id="errslug" class="mb-0 text-danger em"></p>
                  <p class="text-warning mb-0"><small>Changing the title above will NOT change this. Edit it here manually, or click Regenerate to rebuild it from the title above — the old URL redirects (301) automatically either way.</small></p>
                  <style>
                    .slug-input-group { display: flex; gap: 8px; }
                    .slug-input-group input { flex: 1; min-width: 0; }
                    .slug-regen-btn {
                      flex: 0 0 auto; display: inline-flex; align-items: center; gap: 7px;
                      padding: 0 14px; border-radius: 6px; border: 1px solid rgba(21,114,232,.35);
                      background: rgba(21,114,232,.12); color: #6ea8f7; font-size: 12.5px; font-weight: 600;
                      white-space: nowrap; cursor: pointer; transition: background .15s ease, color .15s ease, border-color .15s ease;
                    }
                    .slug-regen-btn:hover:not(:disabled) { background: rgba(21,114,232,.24); border-color: rgba(21,114,232,.55); color: #fff; }
                    .slug-regen-btn:disabled { opacity: .6; cursor: wait; }
                    .slug-regen-btn i { font-size: 11.5px; }
                    .slug-regen-btn.is-loading i { animation: slugRegenSpin .6s linear infinite; }
                    @keyframes slugRegenSpin { to { transform: rotate(360deg); } }
                    #slugInput.slug-just-regenerated { animation: slugRegenFlash 1s ease; }
                    @keyframes slugRegenFlash {
                      0% { box-shadow: 0 0 0 3px rgba(21,114,232,.45); }
                      100% { box-shadow: 0 0 0 0 rgba(21,114,232,0); }
                    }
                  </style>
                  <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        var btn = document.getElementById('regenerateSlugBtn');
                        var input = document.getElementById('slugInput');
                        if (!btn || !input) return;
                        btn.addEventListener('click', function () {
                            var title = (document.querySelector('[name="title"]').value || '').trim();
                            if (!title) { alert('Enter a title first.'); return; }
                            btn.disabled = true;
                            btn.classList.add('is-loading');
                            fetch("{{ route('admin.slug.preview') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ title: title, module: 'blog', id: {{ $blog->id }} }),
                            })
                                .then(function (r) { return r.json(); })
                                .then(function (data) {
                                    if (!data.slug) return;
                                    input.value = data.slug;
                                    input.classList.remove('slug-just-regenerated');
                                    void input.offsetWidth;
                                    input.classList.add('slug-just-regenerated');
                                })
                                .catch(function () { alert('Could not regenerate the URL — try again.'); })
                                .finally(function () {
                                    btn.disabled = false;
                                    btn.classList.remove('is-loading');
                                });
                        });
                    });
                  </script>
                </div>
                <div class="form-group">
                  <label for="">Category **</label>
                  <select class="form-control" name="category">
                    <option value="" selected disabled>Select a category</option>
                    @foreach ($bcats as $key => $bcat)
                      <option value="{{$bcat->id}}" {{$bcat->id == $blog->bcategory->id ? 'selected' : ''}}>{{$bcat->name}}</option>
                    @endforeach
                  </select>
                  <p id="errcategory" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label for="">Content **</label>
                  <textarea id="blogContent" class="form-control summernote" name="content" data-height="300" placeholder="Enter content">{{replaceBaseUrl($blog->content)}}</textarea>
                  <p id="errcontent" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label for="">Serial Number **</label>
                  <input type="number" class="form-control ltr" name="serial_number" value="{{$blog->serial_number}}" placeholder="Enter Serial Number">
                  <p id="errserial_number" class="mb-0 text-danger em"></p>
                  <p class="text-warning"><small>The higher the serial number is, the later the blog will be shown.</small></p>
                </div>
                <div class="form-group">
                  <label>Sidebar **</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="sidebar" value="1" class="selectgroup-input" {{ $blog->sidebar == 1 ? 'checked' : '' }}>
                      <span class="selectgroup-button">Enabled</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="sidebar" value="0" class="selectgroup-input" {{ $blog->sidebar == 0 ? 'checked' : '' }}>
                      <span class="selectgroup-button">Disabled</span>
                    </label>
                  </div>
                  <p id="errsidebar" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label for="">Meta Keywords</label>
                  <input type="text" class="form-control" name="meta_keywords" value="{{$blog->meta_keywords}}" data-role="tagsinput">
                  <p id="errmeta_keywords" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label for="">Meta Description</label>
                  <textarea type="text" class="form-control" name="meta_description" rows="5">{{$blog->meta_description}}</textarea>
                  <p id="errmeta_description" class="mb-0 text-danger em"></p>
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
