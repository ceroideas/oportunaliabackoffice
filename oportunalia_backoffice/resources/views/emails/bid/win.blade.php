
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.bid.win.title')) }}
</h1>

<p class="email-text">{!! __('emails.bid.win.won', compact(['title'])) !!}</p>

<p class="email-text">{{ __('emails.bid.win.contact') }}</p>

<p class="email-text">{{ __('emails.bid.win.salutations') }}</p>
<p class="email-text">{{ __('emails.bid.win.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
