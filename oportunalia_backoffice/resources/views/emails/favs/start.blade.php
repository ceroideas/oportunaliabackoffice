
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{--Str::upper($venta)--}}{{ Str::upper(__('emails.favs.start.title',compact(['venta']))) }}
</h1>

<p class="email-text"><strong>{{ __('emails.favs.start.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">{!! __('emails.favs.start.hasStarted', compact(['venta','title'])) !!}</p>
<p class="email-text">{{ __('emails.favs.start.willStart', compact(['date', 'time','venta'])) }}</p>

<p class="email-text"></p>
<p class="email-text"></p>
<p class="email-text"></p>
<p class="email-text">{{ __('emails.favs.start.salutations') }}</p>
<p class="email-text">{{ __('emails.favs.start.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
