
@extends('emails.layout')

@section('content')

<p class="email-text"><strong>{{ __('emails.direct_sale.end.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text email-red"><strong>{{ __('emails.direct_sale.end.ended', compact(['date', 'time'])) }}</strong></p>
<p class="email-text">{!! __('emails.direct_sale.end.notSold', compact(['title'])) !!}</p>

@include('emails.__direct-sale-large')

@endsection
