@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">Notification History</h4>
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
        <a href="#">Push Notifications</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">Notification History</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">

      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-12">
                    <div class="card-title d-inline-block">Notification History</div>
                </div>
            </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($logs) == 0)
                <h3 class="text-center">NO NOTIFICATION SENT YET</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">Title</th>
                        <th scope="col">Type</th>
                        <th scope="col">Sent By</th>
                        <th scope="col">Recipients</th>
                        <th scope="col">Opened</th>
                        <th scope="col">Failed</th>
                        <th scope="col">Sent At</th>
                        <th scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($logs as $log)
                        <tr>
                          <td>{{$log->title}}</td>
                          <td>
                            @if ($log->notification_type == 'general')
                              <span class="badge badge-info">General</span>
                            @else
                              <span class="badge badge-warning">Personal</span>
                            @endif
                          </td>
                          <td>{{$log->sent_by_name ?? '—'}}</td>
                          <td><span class="badge badge-success">{{$log->recipient_count}}</span></td>
                          <td><span class="badge badge-primary">{{$log->opened_count}}</span></td>
                          <td>
                            @if ($log->failed_count > 0)
                              <span class="badge badge-danger">{{$log->failed_count}}</span>
                            @else
                              <span class="badge badge-secondary">0</span>
                            @endif
                          </td>
                          <td>{{$log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') : '—'}}</td>
                          <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewPush{{$log->id}}">
                              <i class="fas fa-eye"></i> View
                            </button>
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
                {{$logs->links()}}
              </div>
            </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Modals live outside the <table> (invalid/unpredictable when nested inside one). --}}
  @foreach ($logs as $log)
    <div class="modal fade" id="viewPush{{$log->id}}" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{$log->title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>{{$log->message}}</p>
            @if (!empty($log->button_url))
              <p><strong>Button:</strong> {{$log->button_text}} &rarr; <a href="{{$log->button_url}}" target="_blank">{{$log->button_url}}</a></p>
            @endif
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection
