
<div class="email-product large" style="padding:0 10px">
	<img src="{{ $product['image'] }}" height="200" width="100%">
	<div class="info">
		<div class="email-black-big" style="margin-bottom:10px;min-height:54px">{{ $product['title'] }}</div>
		<div class="email-clearfix" style="margin-bottom:16px">
			<div style="float:left">
				<div class="email-small" style="margin-bottom:10px">{{ __('emails.__product.lastOffer') }}</div>
				<div class="email-black-big">{{ $product['lastOffer'] }}€</div>
			</div>
			<div style="float:right">
				<div class="email-small" style="margin-bottom:10px;text-align:right">{{ __('emails.__product.salePrice') }}</div>
				<div class="email-black-big" style="text-align:right">{{ $product['startPrice'] }}€</div>
			</div>
			<div style="text-align:center">
				<div class="email-small" style="margin-bottom:10px">{{ __('emails.__product.offers') }}</div>
				<div class="email-black-big">{{ $product['total_offers'] }}</div>
			</div>
		</div>
		{{-- <div class="email-progress" style="margin-bottom:16px">
			<div class="bar" style="width:100%"></div>
		</div> --}}
		<a class="email-button" href="{{ $product['path'] }}">{{ __('emails.__product.details') }}</a>
	</div>
</div>
