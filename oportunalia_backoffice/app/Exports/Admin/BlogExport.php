<?php

namespace App\Exports\Admin;

use App\Models\Blog;

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

class BlogExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
		$blogQuery = Blog::select(
			"blogs.*",
			"blog_statuses.name as status",
		)
		->join("blog_statuses", "blog_statuses.id", "=", "blogs.status_id");

		return $blogQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.blog.id'),
			__('fields.blog.title'),
			__('fields.blog.show_date'),
			__('fields.blog.publish_date'),
			__('fields.blog.status'),
			__('fields.blog.views'),
			__('fields.blog.created_at'),
			__('fields.blog.updated_at'),
			__('fields.blog.content'),
		];
	}

	public function map($item): array
	{
		return [
			$item->id,
			$item->title,
			$item->show_date,
			$item->publish_date,
			$item->status,
			$item->views,
			$item->created_at,
			$item->updated_at,
			$item->content,
		];
	}

	public function columnFormats(): array
	{
		return [
			'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'C' => \App\Models\Export::SIZE_DATE,
			'D' => \App\Models\Export::SIZE_DATE,
			'E' => \App\Models\Export::SIZE_NORMAL,
			'F' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'G' => \App\Models\Export::SIZE_DATE,
			'H' => \App\Models\Export::SIZE_DATE,
			'I' => \App\Models\Export::SIZE_VERY_LARGE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
