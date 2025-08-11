
@extends('emails.layout')

@section('content')

<h1 class="email-title email-center" style="margin:0 0 35px;">
    {{Str::upper($venta)}}{{ Str::upper(__('emails.favs.to_end.title')) }}
</h1>

<p class="email-text"><strong>{{ __('emails.favs.to_end.hello', compact(['firstname'])) }}</strong></p>
<p class="email-text">{!! __('emails.favs.to_end.willEnd', compact(['title','date', 'time','venta'])) !!}</p>

@if($product->auction_type_id == 1)
    <p class="email-text">{{ __('emails.favs.to_end.aboutEnd', compact(['lastMinutes', 'bidTimeInterval'])) }}</p>
@endif

<p class="email-text">{{ __('emails.favs.to_end.salutations') }}</p>
<p class="email-text">{{ __('emails.favs.to_end.theAdmin') }}</p>
<p class="email-disclaimer">{{ __('emails.layout.disclaimer') }}</p>

@endsection
