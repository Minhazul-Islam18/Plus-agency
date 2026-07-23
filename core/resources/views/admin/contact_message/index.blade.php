@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">Contact Messages</h4>
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
        <a href="#">Contact Messages</a>
      </li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card-title d-inline-block">Messages</div>
                </div>
                <div class="col-lg-6 mt-2 mt-lg-0">
                  <div class="d-flex float-right">
                    <button class="btn btn-danger btn-sm d-none bulk-delete mr-2"
                      data-href="{{route('admin.contact_message.bulk.delete')}}"><i class="flaticon-interface-5"></i> Delete</button>

                    {{-- Status filter --}}
                    <select name="status" class="form-control form-control-sm" style="width: auto;"
                      onchange="window.location='{{ route('admin.contact_messages') }}?status='+this.value">
                      <option value="">All Statuses</option>
                      <option value="pending" {{ request()->input('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                      <option value="approved" {{ request()->input('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                      <option value="rejected" {{ request()->input('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                  </div>
                </div>
            </div>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($contactMessages) == 0)
                <h3 class="text-center">NO MESSAGE FOUND</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">
                          <input type="checkbox" class="bulk-check" data-val="all">
                        </th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Subject</th>
                        <th scope="col">Mail Status</th>
                        <th scope="col">Status</th>
                        <th scope="col">Received</th>
                        <th scope="col">Message</th>
                        <th scope="col">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($contactMessages as $contactMessage)
                      <tr>
                        <td>
                          <input type="checkbox" class="bulk-check" data-val="{{$contactMessage->id}}">
                        </td>
                        <td>{{ $contactMessage->name }}</td>
                        <td>{{ $contactMessage->email }}</td>
                        <td>{{ $contactMessage->subject }}</td>
                        <td>
                          @if ($contactMessage->mail_sent)
                            <span class="badge badge-success">Sent</span>
                          @else
                            <span class="badge badge-danger" data-toggle="tooltip"
                              title="Email delivery failed — check the &quot;To Mail&quot; address under Basic Settings.">Failed</span>
                          @endif
                        </td>
                        <td>
                          @if ($contactMessage->isApproved())
                            <span class="badge badge-success">Approved</span>
                          @elseif ($contactMessage->isRejected())
                            <span class="badge badge-danger">Rejected</span>
                          @else
                            <span class="badge badge-warning">Pending</span>
                          @endif
                          @if ($contactMessage->isReplied())
                            <br><span class="badge badge-info mt-1" data-toggle="tooltip" title="Replied by {{ $contactMessage->replied_by }} on {{ $contactMessage->replied_at->format('d M Y, h:i A') }}">Replied</span>
                          @endif
                        </td>
                        <td>{{ $contactMessage->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                          <a class="btn btn-sm btn-info" href="#" data-toggle="modal" data-target="#contactMessageModal{{ $contactMessage->id }}">Show</a>
                        </td>
                        <td>
                          <div class="d-flex flex-row flex-wrap align-items-center" style="gap: 6px;">
                            @if (!$contactMessage->isApproved())
                              <form class="d-inline-block m-0" action="{{route('admin.contact_message.approve')}}" method="post">
                                @csrf
                                <input type="hidden" name="contact_message_id" value="{{$contactMessage->id}}">
                                <button type="submit" class="btn btn-success btn-sm m-0">
                                  <i class="fas fa-check"></i> Approve
                                </button>
                              </form>
                            @endif
                            @if (!$contactMessage->isRejected())
                              <form class="d-inline-block m-0" action="{{route('admin.contact_message.reject')}}" method="post">
                                @csrf
                                <input type="hidden" name="contact_message_id" value="{{$contactMessage->id}}">
                                <button type="submit" class="btn btn-warning btn-sm m-0">
                                  <i class="fas fa-times"></i> Reject
                                </button>
                              </form>
                            @endif

                            <a class="btn btn-sm btn-primary m-0" href="#" data-toggle="modal" data-target="#replyContactMessageModal{{ $contactMessage->id }}">
                              <i class="fas fa-reply"></i> Reply
                            </a>

                            <form class="deleteform d-inline-block m-0" action="{{route('admin.delete_contact_message')}}" method="post">
                              @csrf
                              <input type="hidden" name="contact_message_id" value="{{$contactMessage->id}}">
                              <button type="submit" class="btn btn-danger btn-sm deletebtn m-0">
                                <span class="btn-label">
                                  <i class="fas fa-trash"></i>
                                </span>
                                Delete
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>

                      @includeIf('admin.contact_message.show_message')
                      @includeIf('admin.contact_message.reply_message')
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
              {{ $contactMessages->appends(['status' => request()->input('status')])->links() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
