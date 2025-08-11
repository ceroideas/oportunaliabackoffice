<?php

namespace App\Exports\Admin;

use App\Models\Auction;

use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DirectSaleExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
		$directSalesQuery = Auction::with([
			'favorites',
			'offers',
			'best_offer' => function($query) { $query->with(['user']); }
		])
		->select(
			"auctions.*",
			"actives.name as active",
			"actives.address",
			"actives.city",
			"provinces.name as province",
			"auction_statuses.name as status",
		)
		->join("actives", "auctions.active_id", "=", "actives.id")
		->join("provinces", "actives.province_id", "=", "provinces.id")
		->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
		->where("auction_type_id", 2);

		return $directSalesQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.direct_sales.id'),
			__('fields.direct_sales.active_id'),
            __('fields.direct_sales.title'),
			__('fields.direct_sales.active'),
			__('fields.direct_sales.status'),
			__('fields.direct_sales.start_date'),
			__('fields.direct_sales.end_date'),
			__('fields.direct_sales.appraisal_value'),
			__('fields.direct_sales.start_price'),
			__('fields.direct_sales.commission'),
			__('fields.direct_sales.favorites'),
			__('fields.direct_sales.offers'),
			__('fields.direct_sales.best_offer'),
			__('fields.direct_sales.best_offerer'),
			__('fields.direct_sales.best_offer_date'),
			__('fields.direct_sales.views'),
			__('fields.direct_sales.created_at'),
			__('fields.direct_sales.updated_at'),
			__('fields.direct_sales.description'),
			__('fields.direct_sales.land_registry'),
			__('fields.direct_sales.technical_specifications'),
			__('fields.direct_sales.conditions'),
            __('fields.direct_sales.link_rewrite'),
            __('fields.direct_sales.deposit'),
		];
	}

	public function map($item): array
	{
		$favorites = count($item->favorites);

		$offers = count($item->offers);

		$best_offer = $item->best_offer ? $item->best_offer->import : '';

		$best_offerer = $item->best_offer ?
			$item->best_offer->user->firstname . ' ' . $item->best_offer->user->lastname
			: '';

		$best_offer_date = $item->best_offer ? $item->best_offer->created_at : '';

		return [
			$item->id,
			$item->active_id,
            $item->auto,
            $item->title,
			$item->active,
			$item->status,
			$item->start_date,
			$item->end_date,
			$item->appraisal_value,
			$item->start_price,
			$item->commission,
			$favorites,
			$offers,
			$best_offer,
			$best_offerer,
			$best_offer_date,
			$item->views,
			$item->created_at,
			$item->updated_at,
			$item->description,
			$item->land_registry,
			$item->technical_specifications,
			$item->conditions,
            'https://oportunalia.com/subasta/'.$item->link_rewrite,
            $item->deposit,
		];
	}

	public function columnFormats(): array
	{
		return [
			'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'I' => NumberFormat::FORMAT_NUMBER_00,
			'J' => NumberFormat::FORMAT_NUMBER_00,
			'M' => NumberFormat::FORMAT_NUMBER_00,
			'N' => NumberFormat::FORMAT_NUMBER_00,
			'Q' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'R' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'X' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Y' => NumberFormat::FORMAT_NUMBER_00,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_ID,
            'C' => \App\Models\Export::SIZE_NORMAL,
            'D' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'E' => \App\Models\Export::SIZE_NAME,
			'F' => \App\Models\Export::SIZE_NORMAL,
			'G' => \App\Models\Export::SIZE_DATE,
			'H' => \App\Models\Export::SIZE_DATE,
			'I' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'J' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'K' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'L' => \App\Models\Export::SIZE_NORMAL,
			'M' => \App\Models\Export::SIZE_NORMAL,
			'N' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'O' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'P' => \App\Models\Export::SIZE_DATE,
			'Q' => \App\Models\Export::SIZE_NORMAL,
			'R' => \App\Models\Export::SIZE_DATE,
			'S' => \App\Models\Export::SIZE_DATE,
			'T' => \App\Models\Export::SIZE_VERY_LARGE,
			'U' => \App\Models\Export::SIZE_VERY_LARGE,
			'V' => \App\Models\Export::SIZE_VERY_LARGE,
			'W' => \App\Models\Export::SIZE_VERY_LARGE,
            'X' => \App\Models\Export::SIZE_VERY_LARGE,
            'Y' => \App\Models\Export::SIZE_NUMBER_SHORT,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
