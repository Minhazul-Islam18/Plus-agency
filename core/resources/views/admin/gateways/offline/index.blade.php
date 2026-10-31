@extends('admin.layout')

@section('styles')
<style>
  .switch { position: relative; display: inline-block; width: 50px; height: 24px; }
  .switch input { opacity: 0; width: 0; height: 0; }
  .switch .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 24px; }
  .switch .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: #fff; transition: .4s; border-radius: 50%; }
  .switch input:checked + .slider { background-color: #1572E8; }
  .switch input:checked + .slider:before { transform: translateX(26px); }
</style>
@endsection

@section('content')
<div class="page-header">
   <h4 class="page-title">Offline Gateways</h4>
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
         <a href="#">Payment Gateways</a>
      </li>
      <li class="separator">
         <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
         <a href="#">Offline Gateways</a>
      </li>
   </ul>
</div>
<div class="row">
   <div class="col-md-12">
      <div class="card">
         <div class="card-header">
            <div class="row">
               <div class="col-lg-4">
                  <div class="card-title d-inline-block">Offline Gateways</div>
               </div>
               <div class="col-lg-3">
                  @if (!empty($langs))
                  <select name="language" class="form-control" onchange="window.location='{{url()->current() . '?language='}}'+this.value">
                     <option value="" selected disabled>Select a Language</option>
                     @foreach ($langs as $lang)
                     <option value="{{$lang->code}}" {{$lang->code == request()->input('language') ? 'selected' : ''}}>{{$lang->name}}</option>
                     @endforeach
                  </select>
                  @endif
               </div>
               <div class="col-lg-4 offset-lg-1 mt-2 mt-lg-0">
                  <a href="#" class="btn btn-primary float-lg-right float-left btn-sm" data-toggle="modal" data-target="#createModal"><i class="fas fa-plus"></i> Add Gateway</a>
               </div>
            </div>
         </div>
         <div class="card-body">
            <div class="row">
               <div class="col-lg-12">
                  @if (count($ogateways) == 0)
                  <h3 class="text-center">NO OFFLINE PAYMENT GATEWAY FOUND</h3>
                  @else
                  <div class="table-responsive">
                     <table class="table table-striped mt-3">
                        <thead>
                           <tr>
                              <th scope="col">#</th>
                              <th scope="col">Name</th>
                              <th scope="col">Short Description</th>
                              <th scope="col">Orders</th>
                              <th scope="col">Last Used</th>
                              <th scope="col">Receipt</th>
                              <th scope="col">Added</th>
                              <th scope="col">Status</th>
                              <th scope="col">Actions</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($ogateways as $key => $ogateway)
                           <tr>
                              <td>{{$ogateway->serial_number}}</td>
                              <td>
                                {{convertUtf8($ogateway->name)}}
                              </td>
                              <td style="max-width:220px">
                                @if (!empty($ogateway->short_description))
                                  {{\Illuminate\Support\Str::limit(convertUtf8($ogateway->short_description), 60)}}
                                @else
                                  <span class="text-muted">—</span>
                                @endif
                              </td>
                              <td>
                                @php $u = $usage[(string) $ogateway->id] ?? null; @endphp
                                @if ($u)
                                  <span class="badge badge-success" title="Completed">{{ (int) $u->completed }}</span>
                                  @if ($u->pending > 0)<span class="badge badge-warning" title="Pending">{{ (int) $u->pending }} pending</span>@endif
                                  <small class="text-muted">/ {{ $u->total }}</small>
                                @else
                                  <span class="text-muted">0</span>
                                @endif
                              </td>
                              <td>{{ $u ? \Carbon\Carbon::parse($u->last_used)->format('d M Y') : '—' }}</td>
                              <td>
                                @if ($ogateway->is_receipt == 1)
                                  <span class="badge badge-info">Required</span>
                                @else
                                  <span class="badge badge-secondary">Not required</span>
                                @endif
                              </td>
                              <td>{{$ogateway->created_at ? $ogateway->created_at->format('d M Y') : '—'}}</td>

                              <td>
                                <label class="switch mb-0">
                                    <input type="checkbox" class="status-toggle" data-id="{{$ogateway->id}}" {{$ogateway->course_checkout_status == 1 ? 'checked' : ''}}>
                                    <span class="slider round"></span>
                                </label>
                              </td>
                              <td width="15%">
                                <a class="btn btn-warning btn-sm editbtn" href="#editModal" data-toggle="modal" data-ogateway_id="{{$ogateway->id}}" data-name="{{$ogateway->name}}" data-short_description="{{$ogateway->short_description}}" data-instructions="{{replaceBaseUrl($ogateway->instructions)}}" data-is_receipt="{{$ogateway->is_receipt}}" data-serial_number="{{$ogateway->serial_number}}" title="Edit">
                                    <i class="far fa-edit"></i>
                                </a>

                                 <form class="deleteform d-inline-block" action="{{route('admin.offline.gateway.delete')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="ogateway_id" value="{{$ogateway->id}}">
                                    <button type="submit" class="btn btn-danger btn-sm deletebtn" title="Delete">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                 </form>
                              </td>
                           </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  @endif
               </div>
            </div>
         </div>
         <div class="card-footer">
            <div class="row">
               <div class="d-inline-block mx-auto">
                  {{$ogateways->appends(['language' => request()->input('language')])->links()}}
               </div>
            </div>
         </div>
      </div>
   </div>
</div>


<!-- Create Offline Gateway Modal -->
@includeIf('admin.gateways.offline.create')



<!-- Edit Package Modal -->
@includeIf('admin.gateways.offline.edit')


@endsection
@section('scripts')
<script>
  $(document).on('change', '.status-toggle', function () {
    var $t = $(this), on = $t.is(':checked');
    $.post('{{ route('admin.offline.status') }}', {
      _token: '{{ csrf_token() }}',
      ogateway_id: $t.data('id'),
      type: 'course',
      course_checkout_status: on ? 1 : 0
    }, function () {
      $.notify({ message: 'Status updated successfully.', icon: 'fa fa-check' }, { type: 'success', placement: { from: 'top', align: 'right' }, time: 1000, delay: 2000 });
    }).fail(function () { $t.prop('checked', !on); });
  });
</script>
<script>
   $(document).ready(function() {

       // make input fields RTL
       $("select[name='language_id']").on('change', function() {
           $(".request-loader").addClass("show");
           let url = "{{url('/')}}/admin/rtlcheck/" + $(this).val();
           // console.log(url);
           $.get(url, function(data) {
               $(".request-loader").removeClass("show");
               if (data == 1) {
                   $("form.modal-form input").each(function() {
                       if (!$(this).hasClass('ltr')) {
                           $(this).addClass('rtl');
                       }
                   });
                   $("form.modal-form select").each(function() {
                       if (!$(this).hasClass('ltr')) {
                           $(this).addClass('rtl');
                       }
                   });
                   $("form.modal-form textarea").each(function() {
                       if (!$(this).hasClass('ltr')) {
                           $(this).addClass('rtl');
                       }
                   });
                   $("form.modal-form .summernote").each(function() {
                       $(this).siblings('.note-editor').find('.note-editable').addClass('rtl text-right');
                   });

               } else {
                   $("form.modal-form input, form.modal-form select, form.modal-form textarea").removeClass('rtl');
                   $("form.modal-form .summernote").siblings('.note-editor').find('.note-editable').removeClass('rtl text-right');
               }
           })
       });
   });
</script>
@endsection
