
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ __('emails.auctions.end-win.title') }}
</h1>

<p class="email-text"><strong>{{ __('emails.auctions.end-win.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text email-red"><strong>{{ __('emails.auctions.end-win.ended', compact(['date', 'time'])) }}</strong></p>
<p class="email-text">{!! __('emails.auctions.end-win.soldTo', compact(['title', 'lastBid', 'lastBidder'])) !!}</p>
<p class="email-text">
	{!! __('emails.auctions.end-win.lookingElse', [
		'link' => '<a href="'.$path.'" class="email-blue"><strong>' .
				__('emails.auctions.end-win.here') .
			'</strong></a>'
	]) !!}
</p>

@include('emails.__auction-large')

<p class="email-text">{{ __('emails.auctions.end-win.salutations') }}</p>
<p class="email-text">{{ __('emails.auctions.end-win.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
