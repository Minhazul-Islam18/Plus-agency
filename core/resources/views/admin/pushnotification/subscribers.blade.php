@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">Push Subscribers</h4>
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
        <a href="#">Subscribers</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">

      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-12">
                    <div class="card-title d-inline-block">Push Subscribers</div>
                </div>
            </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($subscribers) == 0)
                <h3 class="text-center">NO SUBSCRIBER FOUND</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">Subscribed Date</th>
                        <th scope="col">Device</th>
                        <th scope="col">Browser</th>
                        <th scope="col">Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($subscribers as $subscriber)
                        <tr>
                          <td>{{ $subscriber->created_at ? $subscriber->created_at->format('d M Y, H:i') : '—' }}</td>
                          <td>{{ $subscriber->device ?? '—' }}</td>
                          <td>{{ $subscriber->browser ?? '—' }}</td>
                          <td>
                            @if ($subscriber->status == 1)
                              <span class="badge badge-success">Active</span>
                            @else
                              <span class="badge badge-secondary">Inactive</span>
                            @endif
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
                {{$subscribers->links()}}
              </div>
            </div>
        </div>

      </div>
    </div>
  </div>

@endsection
