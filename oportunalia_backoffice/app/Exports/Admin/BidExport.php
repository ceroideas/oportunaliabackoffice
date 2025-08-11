<?php

namespace App\Exports\Admin;

use App\Models\Bid;

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

class BidExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct(int $user_id = null, int $auction_id = null)
	{
		$this->user_id = $user_id;
		$this->auction_id = $auction_id;
	}

	public function collection()
	{
		$bidsQuery = Bid::select(
			"bids.*",
			"auctions.id as reference",
			"users.username",
			"users.firstname",
			"users.lastname",
			"users.document_number"
		)
		->join("auctions", "auctions.id", "=", "bids.auction_id")
		->join("users", "users.id", "=", "bids.user_id")
		->orderBy("bids.id", "asc");

		if ($this->user_id) {
			$bidsQuery->where("users.id", $this->user_id);
		}

		if ($this->auction_id) {
			$bidsQuery->where("auctions.id", $this->auction_id);
		}

		return $bidsQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.bids.id'),
			__('fields.bids.username'),
			__('fields.bids.firstname'),
			__('fields.bids.lastname'),
			__('fields.bids.user_id'),
			__('fields.bids.document_number'),
			__('fields.bids.reference'),
			__('fields.bids.import'),
			__('fields.bids.created_at'),
			__('fields.bids.updated_at'),
            __('fields.bids.auto'),
		];
	}

	public function map($item): array
	{
		/*switch ($item->status) {
			case 1: $status = __('fields.deposit_status.valid'); break;
			case 2: $status = __('fields.deposit_status.invalid'); break;
			default: $status = '';
		}*/

		return [
			$item->id,
			$item->username,
			$item->firstname,
			$item->lastname,
			$item->user_id,
			$item->document_number,
			$item->reference,
			$item->import,
			$item->created_at,
			$item->updated_at,
            $item->auto,
		];
	}

	public function columnFormats(): array
	{
		return [
            'H' =>NumberFormat::FORMAT_NUMBER_00,
			'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'K' => NumberFormat::FORMAT_NUMBER_00,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_NAME,
			'C' => \App\Models\Export::SIZE_NAME,
			'D' => \App\Models\Export::SIZE_SURNAME,
			'E' => \App\Models\Export::SIZE_ID,
			'F' => \App\Models\Export::SIZE_ID,
			'G' => \App\Models\Export::SIZE_ID,
			'H' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'I' => \App\Models\Export::SIZE_DATE,
			'J' => \App\Models\Export::SIZE_DATE,
            'K' => \App\Models\Export::SIZE_NUMBER_SHORT,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
