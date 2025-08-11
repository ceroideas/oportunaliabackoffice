
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.representation.invalid.title')) }}
</h1>
<p class="email-text"><strong>{{ __('emails.representation.invalid.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">{{ __('emails.representation.invalid.rejected') }}</p>
<ul class="email-text">
	<li>{{ __('emails.representation.invalid.representation') }}: {{ $representation }}</li>
	<li>{{ __('emails.representation.invalid.idNumber') }}: {{ $idNumber }}</li>
</ul>
<p class="email-text">{{ __('emails.representation.invalid.atYourService') }}</p>

<p class="email-text">{{ __('emails.representation.invalid.salutations') }}</p>
<p class="email-text">{{ __('emails.representation.invalid.theAdmin') }}</p>
@endsection
