
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ __('emails.deposits.invalid.title') }}
</h1>
<p class="email-text"><strong>{{ __('emails.deposits.invalid.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">
	{{ __('emails.deposits.invalid.confirmation') }} {{ $reference }} (<a href="{{ $path }}">{{ $title }}</a>)
</p>
<p class="email-text">
    {{ __('emails.deposits.invalid.returned') }}
</p>
<p class="email-text">{{ __('emails.deposits.invalid.atYourService') }}</p>

<p class="email-text">{{ __('emails.deposits.invalid.salutations') }}</p>
<p class="email-text">{{ __('emails.deposits.invalid.theAdmin') }}</p>

@endsection
