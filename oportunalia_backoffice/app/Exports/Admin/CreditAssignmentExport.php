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

class CreditAssignmentExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
		$creditAssignmentQuery = Auction::with([
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
		->where("auction_type_id", 4);

		return $creditAssignmentQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.cesions.juzgado'),
            __('fields.cesions.id'),
			__('fields.cesions.active_id'),
            __('fields.cesions.auto'),
            __('fields.cesions.title'),
			__('fields.cesions.active'),
			__('fields.cesions.status'),
			__('fields.cesions.start_date'),
			__('fields.cesions.end_date'),
			__('fields.cesions.appraisal_value'),
			__('fields.cesions.start_price'),
			__('fields.cesions.commission'),
			__('fields.cesions.favorites'),
			__('fields.cesions.offers'),
			__('fields.cesions.best_offer'),
			__('fields.cesions.best_offerer'),
			__('fields.cesions.best_offer_date'),
			__('fields.cesions.views'),
			__('fields.cesions.created_at'),
			__('fields.cesions.updated_at'),
			__('fields.cesions.description'),
			__('fields.cesions.land_registry'),
			__('fields.cesions.technical_specifications'),
			__('fields.cesions.conditions'),
            __('fields.cesions.link_rewrite'),
		];
	}

	public function map($item): array
	{
		$favorites = count($item->favorites);

		$offers = count($item->offers);

		$best_offer = $item->best_offer ? $item->best_offer->import : '';

        if($item->best_offer && $item->best_offer->user){
            $best_offerer = $item->best_offer ?
			$item->best_offer->user->firstname . ' ' . $item->best_offer->user->lastname
			: '';

        }else{
            $best_offerer = '';
        }


		$best_offer_date = $item->best_offer ? $item->best_offer->created_at : '';

		return [
            $item->juzgado, //A
			$item->id,//B
			$item->active_id,//E -> C
            $item->auto,
            $item->title,//C -> D
			$item->active,//D -> E
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
            'https://oportunalia.com/cesion-credito/'.$item->link_rewrite,
		];
	}

	public function columnFormats(): array
	{
		return [
			'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'J' => NumberFormat::FORMAT_NUMBER_00,
			'K' => NumberFormat::FORMAT_NUMBER_00,
			'N' => NumberFormat::FORMAT_NUMBER_00,
			'O' => NumberFormat::FORMAT_NUMBER_00,
			'Q' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'S' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'T' => NumberFormat::FORMAT_DATE_DDMMYYYY,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_VERY_LARGE,
			'B' => \App\Models\Export::SIZE_ID,
			'C' => \App\Models\Export::SIZE_ID,
            'D' => \App\Models\Export::SIZE_NORMAL,
            'E' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'F' => \App\Models\Export::SIZE_NAME,
			'G' => \App\Models\Export::SIZE_NORMAL,
			'H' => \App\Models\Export::SIZE_DATE,
			'I' => \App\Models\Export::SIZE_DATE,
			'J' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'K' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'L' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'M' => \App\Models\Export::SIZE_NORMAL,
			'N' => \App\Models\Export::SIZE_NORMAL,
			'O' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'P' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'Q' => \App\Models\Export::SIZE_DATE,
			'R' => \App\Models\Export::SIZE_NORMAL,
			'S' => \App\Models\Export::SIZE_DATE,
			'T' => \App\Models\Export::SIZE_DATE,
			'U' => \App\Models\Export::SIZE_VERY_LARGE,
			'V' => \App\Models\Export::SIZE_VERY_LARGE,
			'W' => \App\Models\Export::SIZE_VERY_LARGE,
            'X' => \App\Models\Export::SIZE_VERY_LARGE,
            'Y' => \App\Models\Export::SIZE_VERY_LARGE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
