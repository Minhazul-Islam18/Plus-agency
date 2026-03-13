@extends("front.$version.layout")

@section('breadcrumb-subtitle', __('Purchase Submitted!'))
@section('breadcrumb-link', __('Success'))

@section('content')
<div class="checkout-message">
  <div class="container">
    <div class="row">
      <div class="col-lg-12">
        <div class="checkout-success">
          <div class="icon text-success"><i class="far fa-check-circle"></i></div>
          <h2>{{ __('Request Submitted!') }}</h2>
          <p>{{ __('Your tender purchase request has been submitted successfully.') }}</p>
          <p>{{ __('Our team will review your request and contact you shortly.') }}</p>
          <p class="mt-4">{{ __('Thank You.') }}</p>
          <a href="{{ route('tenders') }}" class="main-btn mt-3">{{ __('Browse More Tenders') }}</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
