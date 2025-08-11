
@extends('emails.layout')

@section('content')


<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.recover.title')) }}
</h1>
<p class="email-text"><strong>{{ __('emails.recover.hello', compact('firstname')) }}</strong></p>
<p class="email-text">{{ __('emails.recover.accountRecovery') }}</p>
<p class="email-text">{{ __('emails.recover.clickToReset') }}</p>
<div class="email-center" style="margin-top:32px;margin-bottom:40px">
    <a class="email-button email-subtitle" href="{{ $path }}">{{ __('emails.recover.resetPassword') }}</a>
</div>
<p class="email-text">{{ __('emails.recover.ignoreThis') }}</p>
<p class="email-text">{{ __('emails.recover.salutations') }}</p>
<p class="email-text">{{ __('emails.recover.theAdmin') }}</p>
{{--<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>--}}

@endsection
