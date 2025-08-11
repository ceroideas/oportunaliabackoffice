<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="{{ public_path('pdf-assets/pdf.css') }}" id="maincss">
    </head>
    <body>

        <div class="pdf-header">
            <img src="{{ public_path('pdf-assets/logo.png') }}" width="180">
        </div>


        <div class="text-center uppercase text-navy" style="margin-top:10px">
            <h1>INFORME FINAL</h1>
        </div>

        <div class="clearfix"></div>

        <div class="uppercase text-center text-cyan font-bold">
            {{ $product->title }}
        </div>
        <div class="uppercase text-center text-cyan font-bold">
            REFERENCIA: {{ $product->auto }}
        </div>

        @foreach ($product->images as $i => $image)
	        @if ($i == 0)
		        <div class="main-auction-image--wrapper">
			        <img class="main-auction-image" src="{{ $image['path_pdf'] }}" style="max-height: 300px;">
		        </div>
        	@endif
        @endforeach

        <div class="float-right text-navy" style="margin-top:10px">En Madrid, {{\Carbon\Carbon::now()->format('d/m/Y')}}</div>

        <div class="separation"></div>

        <div class="section-title uppercase text-navy">
            {{ __('pdf.auction-end.description') }}
        </div>

        <div>
            {!! $product->description !!}
        </div>

        <div class="separation"></div>

        @if ($product->bids->count())

            <div class="section-title uppercase text-navy">
                {{ __('pdf.auction-end.bid_history') }}
            </div>

            <table style="width:100%">
                <thead>
                    <tr class="bg-navy uppercase">
                        <th>{{ __('pdf.auction-end.bid') }}</th>
                        <th>{{ __('pdf.auction-end.user') }}</th>
                        <th>{{ __('pdf.auction-end.date') }}</th>
                        <th>{{ __('pdf.auction-end.time') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($product->bids as $i => $bid)
                        <tr class="">
                            <td class="{{ $i%2 == 0 ? 'bg-formblue' : '' }} text-right">
                                {{ number_format($bid->import, 2, ',', '.') }} &euro;
                            </td>
                            <td class="{{ $i%2 == 0 ? 'bg-formblue' : '' }} text-center">
                                #{{ $bid->user->id }}
                            </td>
                            <td class="{{ $i%2 == 0 ? 'bg-formblue' : '' }} text-center">
                                {{ date('d/m/Y', strtotime($bid->created_at)) }}
                            </td>
                            <td class="{{ $i%2 == 0 ? 'bg-formblue' : '' }} text-center">
                                {{ date('H:i', strtotime($bid->created_at)) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="separation"></div>

            <div class="section-title uppercase text-navy">
                {{ __('pdf.auction-end.auctionSummary') }}
            </div>

            <table class="" style="width:100%">
                <tbody>
                    <tr>
                        <td style="width:50%">
                            <img class="table-image" src="{{ public_path('pdf-assets/calendario.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.start_date') }}:
                                {{ date('d/m/Y', strtotime($product->start_date)) }}
                            </div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <img class="table-image" src="{{ public_path('pdf-assets/calendario.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.end_date') }}:
                                {{ date('d/m/Y', strtotime($product->end_date)) }}
                            </div>
                            <div class="clearfix"></div>
                        </td>
                    </tr>
                    <tr class="">
                        <td style="width:50%">
                            <img class="table-image" src="{{ public_path('pdf-assets/visitas.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.visits') }}:
                                {{ $product->views }}
                            </div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <img class="table-image" src="{{ public_path('pdf-assets/puja.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.bids') }}:
                                {{ $product->bids->count() }}
                            </div>
                            <div class="clearfix"></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%">
                            <img class="table-image" src="{{ public_path('pdf-assets/dinero.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.deposit') }}:
                                {{ number_format($product->deposit, 2, ',', '.') }} &euro;
                            </div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <img class="table-image" src="{{ public_path('pdf-assets/dinero.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.start_price') }}:
                                {{ number_format($product->start_price, 2, ',', '.') }} &euro;
                            </div>
                            <div class="clearfix"></div>
                        </td>
                    </tr>

                    <tr class="">
                        <td style="width:50%">
                            <img class="table-image" src="{{ public_path('pdf-assets/dinero.jpg') }}">{{-- Puja ganadora --}}
                            <div class="table-label text-navy">{{ __('pdf.auction-end.winner_bid') }}:
                                {{ number_format($product->last_bid->import, 2, ',', '.') }} &euro;
                            </div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <img class="table-image" src="{{ public_path('pdf-assets/postura.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.adjudicated') }}
                                @if ($product->last_bid->representation)
                                    <div>{{ $product->last_bid->representation->firstname }} </div>
                                    <div>{{ $product->last_bid->representation->lastname }}</div>
                                @else
                                    <div>{{ $product->last_bid->user->firstname }}</div>
                                    <div>{{ $product->last_bid->user->lastname }}</div>
                                @endif
                            </div>
                            <div class="clearfix"></div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="separation"></div>

            {{-- Adjudicatario
            <div class="section-title uppercase text-cyan">
                {{ __('pdf.auction-end.adjudicated') }}
            </div>

            <p>{{ __('pdf.auction-end.declaration') }}:</p>

            <div class="separation"></div>

            <table class="bg-formgray" style="width:100%">
                <tbody>
                    <tr>
                        <td class="border-right-cyan" style="width:35%">
                            <div class="clearfix"></div>
                            <img class="table-image" src="{{ public_path('pdf-assets/icon-speaker.png') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.announcement') }}</div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <p>{{ $product->title }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="border-right-cyan" style="width:35%">
                            <img class="table-image" src="{{ public_path('pdf-assets/icon-file.png') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.adjudicated') }}</div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            @if ($product->last_bid->representation)
                                    <div>{{ $product->last_bid->representation->firstname }} {{ $product->last_bid->representation->lastname }}</div>
                                    <div>{{ __('pdf.auction-end.document_number') }}: {{ $product->last_bid->representation->document_number }}</div>
                                    <div>{{ __('pdf.auction-end.email') }}: {{ $product->last_bid->user->email }}</div>
                                    <div>{{ __('pdf.auction-end.phone') }}: {{ $product->last_bid->user->phone }}</div>
                                    <div>{{ __('pdf.auction-end.address') }}: {{ $product->last_bid->representation->address }}</div>
                                    <div>{{ __('pdf.auction-end.cp') }}: {{ $product->last_bid->representation->cp }}</div>
                                    <div>{{ __('pdf.auction-end.city') }}: {{ $product->last_bid->representation->city }}</div>
                            @else
                                    <div>{{ $product->last_bid->user->firstname }} {{ $product->last_bid->user->lastname }}</div>
                                    <div>{{ __('pdf.auction-end.document_number') }}: {{ $product->last_bid->user->document_number }}</div>
                                    <div>{{ __('pdf.auction-end.email') }}: {{ $product->last_bid->user->email }}</div>
                                    <div>{{ __('pdf.auction-end.phone') }}: {{ $product->last_bid->user->phone }}</div>
                                    <div>{{ __('pdf.auction-end.address') }}: {{ $product->last_bid->user->address }}</div>
                                    <div>{{ __('pdf.auction-end.cp') }}: {{ $product->last_bid->user->cp }}</div>
                                    <div>{{ __('pdf.auction-end.city') }}: {{ $product->last_bid->user->city }}</div>
                            @endif
                        </td>
                    </tr>

                    <tr class="bg-formgray">
                        <td class="border-right-cyan" style="width:35%">
                            <img class="table-image" src="{{ public_path('pdf-assets/icon-banknote.png') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.amount') }}</div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            @if ($product->last_bid)
                                <p>{{ number_format($product->last_bid->import, 2, ',', '.') }} €</p>
                            @endif
                        </td>
                    </tr>
                    <tr class="bg-formgray">
                        <td class="border-right-cyan" style="width:35%">
                            <img class="table-image" src="{{ public_path('pdf-assets/icon-percentage.png') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.commission') }}</div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <p>{{ number_format($product->commission_import, 2, ',', '.') }} €</p>
                        </td>
                    </tr>
                </tbody>
            </table>--}}
            {{-- Fin adjudicatario --}}


        @else
            {{-- Subasta desierta --}}
            <div class="section-title uppercase text-navy">
                {{ __('pdf.auction-end.bid_history') }}
            </div>
            <h1 class="title">La subasta no tuvo pujas</h1>

            <div class="separation"></div>     <div class="separation"></div>

            <div class="section-title uppercase text-navy">
                {{ __('pdf.auction-end.auctionSummary') }}
            </div>


            <table class="" style="width:100%">
                <tbody>
                    <tr>
                        <td style="width:50%">
                            <div class="clearfix"></div>
                            <img class="table-image" src="{{ public_path('pdf-assets/calendario.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.start_date') }}:
                                {{ date('d/m/Y', strtotime($product->start_date)) }}
                            </div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <img class="table-image" src="{{ public_path('pdf-assets/calendario.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.end_date') }}:
                                {{ date('d/m/Y', strtotime($product->end_date)) }}
                            </div>
                            <div class="clearfix"></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:50%">
                            <img class="table-image" src="{{ public_path('pdf-assets/visitas.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.visits') }}:
                                {{ $product->views }}
                            </div>
                            <div class="clearfix"></div>
                        </td>
                        <td>
                            <img class="table-image" src="{{ public_path('pdf-assets/dinero.jpg') }}">
                            <div class="table-label text-navy">{{ __('pdf.auction-end.start_price') }}:
                                {{ number_format($product->start_price, 2, ',', '.') }} &euro;
                            </div>
                            <div class="clearfix"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        @endif

        <div class="separation"></div>

        <div class="favicon-center">
            <img src="{{ public_path('pdf-assets/favicon.png') }}" >
            <div class="text-center text-navy">
                www.oportunalia.com
            </div>
        </div>
    </body>
</html>
