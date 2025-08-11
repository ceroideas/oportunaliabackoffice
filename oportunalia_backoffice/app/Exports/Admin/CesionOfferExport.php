<?php

namespace App\Exports\Admin;

use App\Models\DirectSaleOffer;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CesionOfferExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
        $lessDate = date('Y-m-d', strtotime("-30 days"));

        $directSalesQuery = DirectSaleOffer::with([
            'user',
        ])
         ->join("auctions", "direct_sale_offers.auction_id", "=", "auctions.id")
         ->where("auctions.auction_type_id", 3)
         ->where("direct_sale_offers.created_at", ">",$lessDate);

        return $directSalesQuery->get();
	}

	public function headings(): array
	{
		return [
            __('fields.offers.juzgado'),
            "Referencia",
			__('fields.offers.id'),
			__('fields.offers.user_id'),
            __('fields.offers.username'),
            __('fields.offers.firstname'),
            __('fields.offers.lastname'),
            __('fields.offers.email'),
            "Teléfono",
			__('fields.offers.created_at'),
            __('fields.offers.import'),
            __('fields.offers.auction_id'),
            __('fields.offers.active_id'),
            __('fields.offers.title'),
            __('fields.offers.start_date'),
            __('fields.offers.end_date'),
            __('fields.offers.appraisal_value'),
            __('fields.offers.start_price'),
            __('fields.offers.minimum_offer'),
            __('fields.offers.commission'),
            __('fields.offers.venta'),
            __('fields.offers.url')
		];
	}

	public function map($item): array
	{
        $tipo_venta = $item->auction_type_id == 2 ? 'Venta directa':'Cesion de remate';

		return [
            $item->juzgado,
            $item->auto,
			$item->id,
            $item->user->id,
            $item->user->username,
            $item->user->firstname,
            $item->user->lastname,
            $item->user->email,
            $item->user->phone,
            $item->created_at,
            $item->import,
            $item->auction_id,
            $item->active_id,
            $item->title,
            $item->start_date,
            $item->end_date,
            $item->appraisal_value,
            $item->start_price,
            $item->minimum_bid,
            $item->commission,
            $tipo_venta,
            'https://oportunalia.com/subasta/'.$item->link_rewrite,
		];
	}

	public function columnFormats(): array
	{
		return [
			'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'K' => NumberFormat::FORMAT_NUMBER_00,
			'O' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'P' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'Q' => NumberFormat::FORMAT_NUMBER_00,
			'R' => NumberFormat::FORMAT_NUMBER_00,
			'S' => NumberFormat::FORMAT_NUMBER_00,
            'T' => NumberFormat::FORMAT_NUMBER_00,
		];
	}

	public function columnWidths(): array
	{
		return [
            'A' => \App\Models\Export::SIZE_NORMAL,
            'B' => \App\Models\Export::SIZE_NORMAL,
			'C' => \App\Models\Export::SIZE_ID,
            'D' => \App\Models\Export::SIZE_ID,
			'E' => \App\Models\Export::SIZE_NAME,
			'F' => \App\Models\Export::SIZE_NAME,
			'G' => \App\Models\Export::SIZE_NAME,
			'H' => \App\Models\Export::SIZE_NAME,
			'I' => \App\Models\Export::SIZE_DATE,
			'J' => \App\Models\Export::SIZE_DATE,
			'K' => \App\Models\Export::SIZE_NUMBER_LARGE,
            'L' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'M' => \App\Models\Export::SIZE_NUMBER_SHORT,
            'N' => \App\Models\Export::SIZE_VERY_LARGE,
            'O' => \App\Models\Export::SIZE_DATE,
			'P' => \App\Models\Export::SIZE_DATE,
			'Q' => \App\Models\Export::SIZE_NUMBER_LARGE,
			'R' => \App\Models\Export::SIZE_NUMBER_LARGE,
			'S' => \App\Models\Export::SIZE_NUMBER_LARGE,
			'T' => \App\Models\Export::SIZE_NUMBER_LARGE,
			'U' => \App\Models\Export::SIZE_NORMAL,
            'V' => \App\Models\Export::SIZE_VERY_LARGE
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
