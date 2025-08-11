
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
	{{ Str::upper(__('emails.favs.end.title')) }}
</h1>


<p class="email-text"><strong>{{ __('emails.favs.end-win.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text"><strong>{{ __('emails.favs.end-win.ended', compact(['date', 'time'])) }}</strong></p>
<p class="email-text">{!! __('emails.favs.end-win.soldTo', compact(['title'])) !!}</p>
<p class="email-text">
	{!! __('emails.favs.end-win.lookingElse', [
		'link' => '<a href="'.$path.'" class="email-blue"><strong>' .
				__('emails.favs.end-win.here') .
			'</strong></a>'
	]) !!}
</p>


<p class="email-text">{{ __('emails.favs.end-win.salutations') }}</p>
<p class="email-text">{{ __('emails.favs.end-win.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
