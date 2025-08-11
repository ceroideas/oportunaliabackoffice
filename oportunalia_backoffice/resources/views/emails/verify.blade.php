
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.verify.welcome')) }}
</h1>
<p class="email-text"><strong>{{ __('emails.verify.hello', compact('firstname')) }}</strong></p>
<p class="email-text">{{ __('emails.verify.thankForJoining') }}</p>
<p class="email-text">{{ __('emails.verify.clickToVerify') }}</p>
<div class="email-center" style="margin-top:32px;margin-bottom:40px">
	<a class="email-button email-subtitle" href="{{ $path }}">{{ __('emails.verify.verifyAccount') }}</a>
    <div class="email-center">#Usuario: {{$username}}</div>
    <div class="email-center">#Contraseña: ******* </div>
</div>
<p class="email-text">{{ __('emails.verify.salutations') }}</p>
<p class="email-text">{{ __('emails.verify.theAdmin') }}</p>
{{--
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>
--}}
@endsection
