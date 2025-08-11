<?php

namespace App\Exports\Admin;

use App\Models\Active;

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

class ActiveExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
		$activesQuery = Active::select(
			"actives.*",
			"provinces.name as province",
			"active_categories.name as category",
			"active_conditions.name as condition",
		)
		->join("provinces", "actives.province_id", "=", "provinces.id")
		->join("active_categories", "actives.active_category_id", "=", "active_categories.id")
		->join("active_conditions", "actives.active_condition_id", "=", "active_conditions.id")
		->orderBy("actives.id", "asc");

		return $activesQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.actives.id'),
			__('fields.actives.name'),
			__('fields.actives.category'),
			__('fields.actives.category_id'),
			__('fields.actives.address'),
			__('fields.actives.city'),
			__('fields.actives.province'),
			__('fields.actives.refund'),
			__('fields.actives.condition'),
			__('fields.actives.area'),
			__('fields.actives.created_at'),
			__('fields.actives.updated_at'),
		];
	}

	public function map($item): array
	{
		return [
			$item->id,
			$item->name,
			$item->category,
			$item->active_category_id,
			$item->address,
			$item->city,
			$item->province,
			$item->refund ? '1' : '',
			$item->condition,
			$item->area,
			$item->created_at,
			$item->updated_at,
		];
	}

	public function columnFormats(): array
	{
		return [
			'J' => NumberFormat::FORMAT_NUMBER_00,
			'K' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'L' => NumberFormat::FORMAT_DATE_DDMMYYYY,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_NAME,
			'C' => \App\Models\Export::SIZE_NAME,
			'D' => \App\Models\Export::SIZE_ID,
			'E' => \App\Models\Export::SIZE_ADDRESS,
			'F' => \App\Models\Export::SIZE_LOCALITY,
			'G' => \App\Models\Export::SIZE_PROVINCE,
			'H' => \App\Models\Export::SIZE_BOOL,
			'I' => \App\Models\Export::SIZE_NORMAL,
			'J' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'K' => \App\Models\Export::SIZE_DATE,
			'L' => \App\Models\Export::SIZE_DATE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
