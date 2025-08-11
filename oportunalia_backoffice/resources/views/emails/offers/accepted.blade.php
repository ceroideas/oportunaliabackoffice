
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ __('emails.offers.accepted.title') }}
</h1>

<p class="email-text"><strong>{{ __('emails.offers.accepted.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text email-blue"><strong>{{ __('emails.offers.accepted.accepted', compact(['title'])) }}</strong></p>
<p class="email-text">{!! __('emails.offers.accepted.bestOffer', compact(['lastOffer'])) !!}</p>
<p class="email-text">{{ __('emails.offers.accepted.contact') }}</p>

<div class="email-table">
	<div style="width:50%;float:left">
		<p class="email-label"><strong>{{ __('emails.__product.article') }}</strong></p>
		<p class="email-text" style="margin-top:10px"><a href="{{ $path }}">{{ $title }}</a></p>
	</div>
	<div style="width:50%;float:right">
	</div>
</div>
<div class="email-table">
	<div style="width:50%;float:left">
		<p class="email-label"><strong>{{ __('emails.__product.yourOffer') }}</strong></p>
		<p class="email-text" style="margin-top:10px">{{ $offer->import }}€</p>
	</div>
	<div style="width:50%;float:right">
		<p class="email-label"><strong>{{ __('emails.__product.offerDate') }}</strong></p>
		<p class="email-text" style="margin-top:10px">{{ __('emails.__product.fullDate', ['date' => $offer->date, 'time' => $offer->time]) }}</p>
	</div>
</div>
<div class="email-table">
	<div style="width:50%;float:left">
		<p class="email-label"><strong>{{ __('emails.__product.representation') }}</strong></p>
		<p class="email-text" style="margin-top:10px">{{ $offer->representation }}</p>
	</div>
</div>
<div class="email-table">
	<div style="width:50%;float:left">
		<p class="email-label"><strong>{{ __('emails.__product.salePrice') }}</strong></p>
		<p class="email-text" style="margin-top:10px">{{ $product->startPrice }}€</p>
	</div>
	<div style="width:50%;float:right">
		<p class="email-label"><strong>{{ __('emails.__product.appraisalValue') }}</strong></p>
		<p class="email-text" style="margin-top:10px">{{ $product->appraisalValue }}€</p>
	</div>
</div>

<div class="email-hr"></div>

<div class="email-table" style="margin-bottom:10px">
	<div class="email-black" style="float:left"><strong>{{ __('emails.__product.offerImport') }}</strong></div>
	<div class="email-black" style="float:right"><strong>{{ $offer->import }}€</strong></div>
</div>
<div class="email-table" style="margin-bottom:10px">
	<div style="float:left;margin-left:10px">{{ __('emails.__product.commission', compact(['commission'])) }}</div>
	<div style="float:right">{{ $product->commission_import }}€</div>
</div>

<div class="email-hr"></div>

<div class="email-table" style="margin-bottom:10px">
	<div class="email-black" style="float:left"><strong>{{ __('emails.__product.total') }}</strong></div>
	<div class="email-black" style="float:right"><strong>{{ $product->total }}€</strong></div>
</div>

<div style="margin-top:40px;margin-bottom:40px">
	@include('emails.__direct-sale-large')
</div>

<p class="email-text">{{ __('emails.offers.accepted.salutations') }}</p>
<p class="email-text">{{ __('emails.offers.accepted.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
