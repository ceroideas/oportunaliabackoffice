<?php

namespace App\Exports\Admin;

use App\Models\Deposit;

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

class DepositExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct(int $user_id = null, int $auction_id = null)
	{
		$this->user_id = $user_id;
		$this->auction_id = $auction_id;
	}

	public function collection()
	{
		$depositsQuery = Deposit::select(
			"deposits.*",
			"auctions.id as reference",
			"users.username",
			"users.firstname",
			"users.lastname",
			"users.document_number"
		)
		->join("auctions", "auctions.id", "=", "deposits.auction_id")
		->join("users", "users.id", "=", "deposits.user_id")
		->orderBy("deposits.id", "asc");

		if ($this->user_id) {
			$depositsQuery->where("users.id", $this->user_id);
		}

		if ($this->auction_id) {
			$depositsQuery->where("auctions.id", $this->auction_id);
		}

		return $depositsQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.deposits.id'),
			__('fields.deposits.username'),
			__('fields.deposits.firstname'),
			__('fields.deposits.lastname'),
			__('fields.deposits.user_id'),
			__('fields.deposits.document_number'),
			__('fields.deposits.reference'),
			__('fields.deposits.status'),
			__('fields.deposits.created_at'),
			__('fields.deposits.updated_at'),
		];
	}

	public function map($item): array
	{
		switch ($item->status) {
			case 1: $status = __('fields.deposit_status.valid'); break;
			case 2: $status = __('fields.deposit_status.invalid'); break;
			default: $status = '';
		}

		return [
			$item->id,
			$item->username,
			$item->firstname,
			$item->lastname,
			$item->user_id,
			$item->document_number,
			$item->reference,
			$status,
			$item->created_at,
			$item->updated_at,
		];
	}

	public function columnFormats(): array
	{
		return [
			'I' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'J' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
			'H' => \App\Models\Export::SIZE_NORMAL,
			'I' => \App\Models\Export::SIZE_DATE,
			'J' => \App\Models\Export::SIZE_DATE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
