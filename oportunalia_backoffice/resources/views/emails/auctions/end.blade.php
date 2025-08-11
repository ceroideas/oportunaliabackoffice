
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.auctions.end.title')) }}
</h1>
{{--<p class="email-text"><strong>{{ __('emails.auctions.end.hello', compact(['firstname'])) }}</strong></p>--}}
<p class="email-text">{!! __('emails.auctions.end.ended', compact(['title'])) !!}</p>
<p class="email-text">{{ __('emails.auctions.end.noBid', compact(['title'])) }}</p>
<p class="email-text">{{ __('emails.auctions.end.lookingElse') }}</p>

{{--@include('emails.__auction-large')--}}

<p class="email-text">{{ __('emails.auctions.end.salutations') }}</p>
<p class="email-text">{{ __('emails.auctions.end.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
