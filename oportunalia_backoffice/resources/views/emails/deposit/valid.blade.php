
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.deposits.valid.title')) }}
</h1>
<p class="email-text"><strong>{{ __('emails.deposits.valid.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">{{ __('emails.deposits.valid.confirmation') }} {{ $reference }} (<a href="{{ $path }}">{{ $title }}</a>)</p>
<p class="email-text">{{ __('emails.deposits.valid.atYourService') }}</p>

<p class="email-text">{{ __('emails.deposits.valid.salutations') }}</p>
<p class="email-text">{{ __('emails.deposits.valid.theAdmin') }}</p>
@endsection
