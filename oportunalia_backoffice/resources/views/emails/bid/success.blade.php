
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.bid.success.title')) }}
</h1>

<p class="email-text"><strong>{{ __('emails.bid.success.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">{!! __('emails.bid.success.bidCorrect', compact(['title'])) !!}
    <strong>
        {{-- number_format($bid->import, 2, ",", ".") --}}
        {{ $bid->import }} &euro;
    </strong>
    {{ __('emails.bid.success.bidCorrect2') }} </p>

<p class="email-text">{{ __('emails.bid.success.bestBidder') }}</p>


<p class="email-text">{{ __('emails.bid.success.salutations') }}</p>
<p class="email-text">{{ __('emails.bid.success.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
