@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">Push Notification Statistics</h4>
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
        <a href="#">Statistics</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-sm-6 col-md-4">
      <div class="card card-stats card-info card-round">
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <div class="icon-big text-center"><i class="fas fa-users"></i></div>
            </div>
            <div class="col-9 col-stats pl-1">
              <div class="numbers">
                <p class="card-category">Total Subscribers</p>
                <h4 class="card-title">{{ number_format($total) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card card-stats card-success card-round">
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <div class="icon-big text-center"><i class="fas fa-user-check"></i></div>
            </div>
            <div class="col-9 col-stats pl-1">
              <div class="numbers">
                <p class="card-category">Active Subscribers</p>
                <h4 class="card-title">{{ number_format($active) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card card-stats card-secondary card-round">
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <div class="icon-big text-center"><i class="fas fa-user-slash"></i></div>
            </div>
            <div class="col-9 col-stats pl-1">
              <div class="numbers">
                <p class="card-category">Inactive Subscribers</p>
                <h4 class="card-title">{{ number_format($inactive) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card card-stats card-dark card-round">
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <div class="icon-big text-center"><i class="fas fa-paper-plane"></i></div>
            </div>
            <div class="col-9 col-stats pl-1">
              <div class="numbers">
                <p class="card-category">Notifications Sent</p>
                <h4 class="card-title">{{ number_format($notificationsSent) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card card-stats card-warning card-round">
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <div class="icon-big text-center"><i class="fas fa-inbox"></i></div>
            </div>
            <div class="col-9 col-stats pl-1">
              <div class="numbers">
                <p class="card-category">Total Deliveries</p>
                <h4 class="card-title">{{ number_format($totalRecipients) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card card-stats card-primary card-round">
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <div class="icon-big text-center"><i class="fas fa-mouse-pointer"></i></div>
            </div>
            <div class="col-9 col-stats pl-1">
              <div class="numbers">
                <p class="card-category">Total Opened</p>
                <h4 class="card-title">{{ number_format($totalOpened) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6 col-md-4">
      <div class="card card-stats card-danger card-round">
        <div class="card-body">
          <div class="row">
            <div class="col-3">
              <div class="icon-big text-center"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="col-9 col-stats pl-1">
              <div class="numbers">
                <p class="card-category">Total Failed Deliveries</p>
                <h4 class="card-title">{{ number_format($totalFailed) }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
