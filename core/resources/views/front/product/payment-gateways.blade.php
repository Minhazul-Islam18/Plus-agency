{{-- Start: Stripe Area --}}
@if ($stripe->status == 1)
<div class="option-block">
    <div class="checkbox">
        <label>
            <input name="method" class="input-check" type="radio" value="stripe" data-tabid="stripe" data-action="{{route('product.stripe.submit')}}">
            <span>{{__('Stripe')}}</span>
        </label>
    </div>
</div>


<div class="row gateway-details" id="tab-stripe">

    <div class="col-md-6 mb-4">
        <div class="field-label">{{__('Card Number')}} *</div>
        <div class="field-input">
            <input type="text" class="card-elements" name="cardNumber" placeholder="{{ __('Card Number')}}" autocomplete="off" oninput="validateCard(this.value);" />
        </div>
        @error('cardNumber')
        <p class="text-danger">{{convertUtf8($message)}}</p>
        @enderror
        <span id="errCard" class="text-danger"></span>
    </div>
    <div class="col-md-6 mb-4">
        <div class="field-label">{{__('CVC')}} *</div>
        <div class="field-input">
            <input type="text" class="card-elements" placeholder="{{ __('CVC') }}" name="cardCVC" oninput="validateCVC(this.value);">
        </div>
        @error('cardCVC')
        <p class="text-danger">{{convertUtf8($message)}}</p>
        @enderror
        <span id="errCVC text-danger"></span>
    </div>
    <div class="col-md-6 mb-4">
        <div class="field-label">{{__('Month')}} *</div>
        <div class="field-input">
            <input type="text" class="card-elements" placeholder="{{__('Month')}}" name="month">
        </div>
        @error('month')
        <p class="text-danger">{{convertUtf8($message)}}</p>
        @enderror
    </div>
    <div class="col-md-6 mb-4">
        <div class="field-label">{{__('Year')}} *</div>
        <div class="field-input">
            <input type="text" class="card-elements" placeholder="{{__('Year')}}" name="year">
        </div>
        @error('year')
        <p class="text-danger">{{convertUtf8($message)}}</p>
        @enderror
    </div>
</div>
@endif
{{-- End: Stripe Area --}}


{{-- Start: Razorpay Area --}}
@if ($razorpay->status == 1)
<div class="option-block">
    <div class="radio-block">
        <div class="checkbox">
            <label>
                <input name="method" type="radio" class="input-check" value="razorpay" data-tabid="razorpay" data-action="{{route('product.razorpay.submit')}}">
                <span>{{__('Razorpay')}}</span>
            </label>
        </div>
    </div>
</div>

<div class="row gateway-details" id="tab-razorpay">
    <input type="hidden" name="method" value="Razorpay">
</div>
@endif
{{-- End: Razorpay Area --}}


{{-- Start: Moneroo Area --}}
@if ($moneroo->status == 1)
<div class="option-block">
    <div class="radio-block">
        <div class="checkbox">
            <label>
                <input name="method" type="radio" class="input-check" value="moneroo" data-tabid="moneroo" data-action="{{route('product.moneroo.submit')}}">
                <span>{{__('Moneroo')}}</span>
            </label>
        </div>
    </div>
</div>

<div class="row gateway-details" id="tab-moneroo">
    <input type="hidden" name="method" value="Moneroo">
</div>
@endif
{{-- End: Moneroo Area --}}


{{-- Start: Offline Gateways Area --}}
@foreach ($ogateways as $ogateway)
    <div class="option-block">
        <div class="checkbox">
            <label>
            <input name="method" class="input-check" type="radio" value="{{$ogateway->id}}" data-tabid="{{$ogateway->id}}" data-action="{{route('product.offline.submit', $ogateway->id)}}">
                <span>{{$ogateway->name}}</span>
            </label>
        </div>
    </div>

    <p class="gateway-desc">{{$ogateway->short_description}}</p>

    <div class="gateway-details row" id="tab-{{$ogateway->id}}">
        <div class="col-12">
            <div class="gateway-instruction">
                {!! replaceBaseUrl($ogateway->instructions) !!}
            </div>
        </div>

        @if ($ogateway->is_receipt == 1)
            <div class="col-12 mb-4">
                <label for="" class="d-block">{{__('Receipt')}} **</label>
                <input type="file" name="receipt">
                <p class="mb-0 text-warning">** {{__('Receipt image must be .jpg / .jpeg / .png')}}</p>
            </div>
        @endif
    </div>
@endforeach


@if ($errors->has('receipt'))
    <p class="text-danger mb-4">{{$errors->first('receipt')}}</p>
@endif
{{-- End: Offline Gateways Area --}}



<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="lc" value="UK">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="ref_id" id="ref_id" value="">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynow_LG.gif:NonHostedGuest">
<input type="hidden" name="currency_sign" value="$">

