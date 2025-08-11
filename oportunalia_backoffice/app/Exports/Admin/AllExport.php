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

class AllExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
		$auctionsQuery = Auction::with([
			'favorites',
			'deposits',
			'bids',
			'last_bid' => function($query) { $query->with(['user']); }
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
		->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id");
		//->where("auction_type_id", 1);

		return $auctionsQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.auctions.id'),
			__('fields.auctions.title'),
			__('fields.auctions.active'),
			__('fields.auctions.active_id'),
			__('fields.auctions.status'),
			__('fields.auctions.start_date'),
			__('fields.auctions.end_date'),
			__('fields.auctions.appraisal_value'),
			__('fields.auctions.start_price'),
			__('fields.auctions.deposit'),
			__('fields.auctions.commission'),
			__('fields.auctions.bid_price_interval'),
			__('fields.auctions.bid_time_interval'),
			__('fields.auctions.favorites'),
			__('fields.auctions.deposits'),
			__('fields.auctions.bids'),
			__('fields.auctions.last_bid'),
			__('fields.auctions.last_bidder'),
			__('fields.auctions.last_bid_date'),
			__('fields.auctions.views'),
			__('fields.auctions.created_at'),
			__('fields.auctions.updated_at'),
			__('fields.auctions.description'),
			__('fields.auctions.land_registry'),
			__('fields.auctions.technical_specifications'),
			__('fields.auctions.conditions'),
            __('fields.auctions.link_rewrite'),
			__('fields.auctions.auction_type_id'),
		];
	}

	public function map($item): array
	{
		$favorites = count($item->favorites);

		$deposits = 0;

		foreach ($item->deposits as $deposit)
		{
			if ($deposit->status == 1) { $deposits++; }
		}

		$bids = count($item->bids);

		$last_bid = $item->last_bid ? $item->last_bid->import : '';

		$last_bidder="";

		if($item->last_bid && $item->last_bid->user){
			$last_bidder = $item->last_bid ? $item->last_bid->user->firstname . ' ' . $item->last_bid->user->lastname : '';
		}


		$last_bid_date = $item->last_bid ? $item->last_bid->created_at : '';

		return [
			$item->id,
			$item->title,
			$item->active,
			$item->active_id,
			$item->status,
			$item->start_date,
			$item->end_date,
			$item->appraisal_value,
			$item->start_price,
			$item->deposit,
			$item->commission,
			$item->bid_price_interval,
			$item->bid_time_interval,
			$favorites,
			$deposits,
			$bids,
			$last_bid,
			$last_bidder,
			$last_bid_date,
			$item->views,
			$item->created_at,
			$item->updated_at,
			$item->description,
			$item->land_registry,
			$item->technical_specifications,
			$item->conditions,
            'https://oportunalia.com/subasta/'.$item->link_rewrite,
			$item->auction_type_id
		];
	}

	public function columnFormats(): array
	{
		return [
			'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'H' => NumberFormat::FORMAT_NUMBER_00,
			'I' => NumberFormat::FORMAT_NUMBER_00,
			'J' => NumberFormat::FORMAT_NUMBER_00,
			'L' => NumberFormat::FORMAT_NUMBER_00,
			'Q' => NumberFormat::FORMAT_NUMBER_00,
			'S' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'U' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'V' => NumberFormat::FORMAT_DATE_DDMMYYYY,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'C' => \App\Models\Export::SIZE_NAME,
			'D' => \App\Models\Export::SIZE_ID,
			'E' => \App\Models\Export::SIZE_NORMAL,
			'F' => \App\Models\Export::SIZE_DATE,
			'G' => \App\Models\Export::SIZE_DATE,
			'H' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'I' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'J' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'K' => \App\Models\Export::SIZE_NORMAL,
			'L' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'M' => \App\Models\Export::SIZE_NORMAL,
			'N' => \App\Models\Export::SIZE_NORMAL,
			'O' => \App\Models\Export::SIZE_NORMAL,
			'P' => \App\Models\Export::SIZE_NORMAL,
			'Q' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'R' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'S' => \App\Models\Export::SIZE_DATE,
			'T' => \App\Models\Export::SIZE_NORMAL,
			'U' => \App\Models\Export::SIZE_DATE,
			'V' => \App\Models\Export::SIZE_DATE,
			'W' => \App\Models\Export::SIZE_VERY_LARGE,
			'X' => \App\Models\Export::SIZE_VERY_LARGE,
			'Y' => \App\Models\Export::SIZE_VERY_LARGE,
			'Z' => \App\Models\Export::SIZE_VERY_LARGE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
