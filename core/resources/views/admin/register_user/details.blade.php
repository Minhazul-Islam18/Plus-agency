@extends('admin.layout')
@section('content')
<div class="page-header">
   <h4 class="page-title">Customer Details</h4>
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
         <a href="{{url()->previous()}}">Customers</a>
      </li>
      <li class="separator">
         <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
         <a href="#">Customer Details</a>
      </li>
   </ul>

   <a href="{{ url()->previous() }}" class="btn-md btn btn-primary" style="margin-left: auto;">Back</a>
</div>
<div class="row">
   <div class="col-md-12">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <div class="h4 card-title">
                                Profile Picture
                            </div>
                        </div>
                        <div class="card-body text-center py-4">
                            @if (strpos($user->photo, 'facebook') !== false || strpos($user->photo, 'google'))
                              <img class="rounded-circle" src="{{$user->photo ? $user->photo : asset('assets/front/img/user/profile.jpg')}}" alt="" width="150">
                          @else
                              <img src="{{!empty($user->photo) ? asset('assets/front/img/user/'.$user->photo) : ''}}" alt="" width="150">
                          @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">{{__('Customer Details')}}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('First Name:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->fname}}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('Last Name:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->lname}}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('Username:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->username}}
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('Email:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->email}}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('Number:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->number}}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('Country:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->country}}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('City:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->city}}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-lg-6">
                                    <strong>{{__('Address:')}}</strong>
                                </div>
                                <div class="col-lg-6">
                                    {{$user->address}}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-md-9">
                    <div class="row">

                        <div class="col-md-6">
                         <div class="card">
                             <div class="card-header">
                                 <h4 class="card-title">{{__('Shipping Details')}}</h4>
                             </div>
                             <div class="card-body">
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Email:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_email}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Phone:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_number}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('City:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_city}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Address:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_address}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Country:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_country}}
                                     </div>
                                 </div>

                             </div>
                         </div>

                        </div>
                        <div class="col-md-6">
                         <div class="card">
                             <div class="card-header">
                                 <h4 class="card-title">{{__('Billing Details')}}</h4>
                             </div>
                             <div class="card-body">
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Email:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_email}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Phone:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_number}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('City:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_city}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Address:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_address}}
                                     </div>
                                 </div>
                                 <div class="row mb-2">
                                     <div class="col-lg-6">
                                         <strong>{{__('Country:')}}</strong>
                                     </div>
                                     <div class="col-lg-6">
                                         {{$user->billing_country}}
                                     </div>
                                 </div>

                             </div>
                         </div>

                        </div>
                    </div>
                </div>

            </div>


   </div>
</div>
@endsection
