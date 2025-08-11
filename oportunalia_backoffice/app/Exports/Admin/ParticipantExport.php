<?php

namespace App\Exports\Admin;

use App\Models\Deposit;
use App\Models\Participant;

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

class ParticipantExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	/* public function __construct(int $user_id = null, int $auction_id = null)
	{
		$this->user_id = $user_id;
		$this->auction_id = $auction_id;
	} */
    public function __construct(){

    }

	public function collection()
	{
		$participantsQuery = Participant::select(
			"participants.*",
		)
		->orderBy("participants.created_at", "asc")
        ->groupBy('participants.email');

        $participantsQuery->where("participants.campaign_id", "8");
		/* if ($this->user_id) {
			$depositsQuery->where("users.id", $this->user_id);
		}

		if ($this->auction_id) {
			$depositsQuery->where("auctions.id", $this->auction_id);
		} */

		return $participantsQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.participants.id'),
            __('fields.participants.firstname'),
			__('fields.participants.lastname'),
			__('fields.participants.email'),
			__('fields.participants.code'),
			__('fields.participants.created_at')
		];
	}

	public function map($item): array
	{
		return [
			//$item->id,
            8,
			$item->firstname,
			$item->lastname,
			$item->email,
			$item->code,
			$item->created_at,
		];
	}

	public function columnFormats(): array
	{
		return [
			'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_NAME,
			'C' => \App\Models\Export::SIZE_SURNAME,
			'D' => \App\Models\Export::SIZE_NORMAL,
			'E' => \App\Models\Export::SIZE_ID,
			'F' => \App\Models\Export::SIZE_DATE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}


}
