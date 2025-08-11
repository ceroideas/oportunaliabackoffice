
@extends('emails.layout')

@section('content')

<p class="email-text"><strong>{{ __('emails.cesion.end.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text email-red"><strong>{{ __('emails.cesion.end.ended', compact(['date', 'time'])) }}</strong></p>
<p class="email-text">{!! __('emails.cesion.end.notSold', compact(['title'])) !!}</p>

@include('emails.__direct-sale-large')

@endsection
