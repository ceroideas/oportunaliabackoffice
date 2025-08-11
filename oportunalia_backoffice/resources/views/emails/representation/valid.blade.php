
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.representation.valid.title')) }}
</h1>
<p class="email-text"><strong>{{ __('emails.representation.valid.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">{{ __('emails.representation.valid.confirmation') }}:</p>
<ul class="email-text">
	<li>{{ __('emails.representation.valid.representation') }}: {{ $representation }}</li>
	<li>{{ __('emails.representation.valid.idNumber') }}: {{ $idNumber }}</li>
</ul>
<p class="email-text">{{ __('emails.representation.valid.atYourService') }}</p>

<p class="email-text">{{ __('emails.representation.valid.salutations') }}</p>
<p class="email-text">{{ __('emails.representation.valid.theAdmin') }}</p>
@endsection
