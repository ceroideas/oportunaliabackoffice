
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{Str::upper($venta)}}{{ Str::upper(__('emails.favs.end.title')) }}
</h1>

<p class="email-text"><strong>{{ __('emails.favs.end.hello', compact(['firstname'])) }}</strong></p>


@if($product->auction_type_id == 3)
    <p class="email-text">{!! __('emails.favs.end.withoutOffer', compact(['title','venta'])) !!}</p>
@else
    <p class="email-text">{!! __('emails.favs.end.withoutBidder', compact(['title','venta'])) !!}</p>
@endif

@if($product->auction_type_id == 1)
    <p class="email-text">{{ __('emails.favs.end.unsoldBid') }}</p>
@else
    <p class="email-text">{{ __('emails.favs.end.unsold') }}</p>
@endif

<p class="email-text">{{ __('emails.favs.end.ended') }}</p>


<p class="email-text">{{ __('emails.favs.end.salutations') }}</p>
<p class="email-text">{{ __('emails.favs.end.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
