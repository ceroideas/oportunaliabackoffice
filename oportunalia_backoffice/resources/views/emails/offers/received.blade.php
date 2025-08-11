
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.offers.received.title')) }}

</h1>

<p class="email-text"><strong>{{ __('emails.offers.received.hello',compact(['firstname'])) }} </strong></p>
<p class="email-text">{!! __('emails.offers.received.accepted',compact(['title','import'])) !!} </p>

{{--
<p class="email-text">{{ __('emails.offers.received.contact') }}</p>

<div class="email-table">
	<div style="width:50%;float:left">
		<p class="email-label"><strong>{{ __('emails.__product.article') }}</strong></p>
		<p class="email-text" style="margin-top:10px"><a href="https://oportunalia.com/subasta/{{ $guid }}">{{ $title }}</a></p>
	</div>
	<div style="width:50%;float:right">
	</div>
</div>

<div class="email-hr"></div>

<div class="email-table">
	<div style="width:50%;float:left">
		<p class="email-label"><strong>{{ __('emails.__product.yourOffer') }}</strong></p>
		<p class="email-text" style="margin-top:10px">{{ $import }}€</p>
	</div>
	<div style="width:50%;float:right">
		<p class="email-label"><strong>{{ __('emails.__product.offerDate') }}</strong></p>
		<p class="email-text" style="margin-top:10px">{{ $created_at }}</p>
	</div>
</div>

<div class="email-hr"></div>
--}}


<p class="email-text">{{ __('emails.offers.received.salutations') }}</p>
<p class="email-text">{{ __('emails.offers.received.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
