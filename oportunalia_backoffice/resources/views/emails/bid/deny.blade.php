
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.bid.deny.title')) }}
</h1>
<p class="email-text"><strong>{{ __('emails.bid.deny.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">{!! __('emails.bid.deny.outbidden', compact(['title'])) !!}</p>

<p class="email-text">{{ __('emails.bid.deny.salutations') }}</p>
<p class="email-text">{{ __('emails.bid.deny.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>
@endsection
