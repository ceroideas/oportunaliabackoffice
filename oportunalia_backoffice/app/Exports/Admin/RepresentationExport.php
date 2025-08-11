<?php

namespace App\Exports\Admin;

use App\Models\Representation;

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

class RepresentationExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct(int $user_id = null)
	{
		$this->user_id = $user_id;
	}

	public function collection()
	{
		$representationsQuery = Representation::with("image")
			->select(
				"representations.*",
				"users.firstname as user_firstname",
				"users.lastname as user_lastname",
				"representation_types.name as representation_type",
				"countries.name as country",
				"provinces.name as province",
			)
			->join("users", "users.id", "=", "representations.user_id")
			->join("representation_types", "representation_types.id", "=", "representations.representation_type_id")
			->join("countries", "countries.id", "=", "representations.country_id")
			->join("provinces", "provinces.id", "=", "representations.province_id")
			->orderBy("representations.id", "asc");

		if ($this->user_id) {
			$representationsQuery->where("users.id", $this->user_id);
		}

		return $representationsQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.representations.id'),
			__('fields.representations.alias'),
			__('fields.representations.firstname'),
			__('fields.representations.lastname'),
			__('fields.representations.document_number'),
			__('fields.representations.representing'),
			__('fields.representations.user_id'),
			__('fields.representations.address'),
			__('fields.representations.cp'),
			__('fields.representations.city'),
			__('fields.representations.province'),
			__('fields.representations.country'),
			__('fields.representations.type'),
			__('fields.representations.status'),
			__('fields.representations.created_at'),
			__('fields.representations.updated_at'),
		];
	}

	public function map($item): array
	{
		switch ($item->status) {
			case 1: $status = __('fields.representation_status.valid'); break;
			case 2: $status = __('fields.representation_status.invalid'); break;
			default: $status = '';
		}

		return [
			$item->id,
			$item->alias,
			$item->firstname,
			$item->lastname,
			$item->document_number,
			$item->user_firstname . ' ' .$item->user_lastname,
			$item->user_id,
			$item->address,
			$item->cp,
			$item->city,
			$item->province,
			$item->country,
			$item->representation_type,
			$status,
			$item->created_at,
			$item->updated_at,
		];
	}

	public function columnFormats(): array
	{
		return [
			'O' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'P' => NumberFormat::FORMAT_DATE_DDMMYYYY,
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
			'F' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'G' => \App\Models\Export::SIZE_ID,
			'H' => \App\Models\Export::SIZE_ADDRESS,
			'I' => \App\Models\Export::SIZE_ZIPCODE,
			'J' => \App\Models\Export::SIZE_LOCALITY,
			'K' => \App\Models\Export::SIZE_PROVINCE,
			'L' => \App\Models\Export::SIZE_COUNTRY,
			'M' => \App\Models\Export::SIZE_NORMAL,
			'N' => \App\Models\Export::SIZE_NORMAL,
			'O' => \App\Models\Export::SIZE_DATE,
			'P' => \App\Models\Export::SIZE_DATE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
