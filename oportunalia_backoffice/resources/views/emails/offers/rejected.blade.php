
@extends('emails.layout')

@section('content')

<p class="email-text"><strong>{{ __('emails.offers.rejected.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">
	{{ __('emails.offers.rejected.confirmation') }} {{ $reference }} (<a href="{{ $path }}">{{ $title }}</a>)
	{{ __('emails.offers.rejected.rejected') }}
</p>
<p class="email-text">{{ __('emails.offers.rejected.atYourService') }}</p>

@endsection
