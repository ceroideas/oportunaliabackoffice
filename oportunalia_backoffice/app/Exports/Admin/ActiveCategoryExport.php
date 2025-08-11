<?php

namespace App\Exports\Admin;

use App\Models\ActiveCategory;

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

class ActiveCategoryExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
		$activeCategoryQuery = ActiveCategory::select("active_categories.*");

		return $activeCategoryQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.active_categories.id'),
			__('fields.active_categories.name'),
			__('fields.active_categories.description'),
			__('fields.active_categories.deleted_at'),
		];
	}

	public function map($item): array
	{
		return [
			$item->id,
			$item->name,
			$item->description,
			$item->deleted_at,
		];
	}

	public function columnFormats(): array
	{
		return [
			'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_NAME,
			'C' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'D' => \App\Models\Export::SIZE_DATE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
