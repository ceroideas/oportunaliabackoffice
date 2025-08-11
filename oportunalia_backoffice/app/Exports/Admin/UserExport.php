<?php

namespace App\Exports\Admin;

use App\Models\User;

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

class UserExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
	}

	public function collection()
	{
		$usersQuery = User::select("users.*", "roles.description as role")
			->join("roles", "users.role_id", "=", "roles.id")
			->leftJoin("provinces", "users.province_id", "=", "provinces.id")
			->leftJoin("countries", "users.country_id", "=", "countries.id")
			->orderBy("users.id", "asc");

		return $usersQuery->get();
	}

	public function headings(): array
	{
		return [
			__('fields.users.id'),
			__('fields.users.username'),
			__('fields.users.firstname'),
			__('fields.users.lastname'),
			__('fields.users.role'),
			__('fields.users.birthdate'),
			__('fields.users.email'),
			__('fields.users.phone'),
			__('fields.users.address'),
			__('fields.users.cp'),
			__('fields.users.city'),
			__('fields.users.province'),
			__('fields.users.country'),
			__('fields.users.document_number'),
			__('fields.users.lang'),
			__('fields.users.confirmed'),
			__('fields.users.status'),
			__('fields.users.notification_news'),
			__('fields.users.notification_auctions'),
			__('fields.users.notification_favorites'),
			__('fields.users.number_login'),
			__('fields.users.created_at'),
			__('fields.users.updated_at'),
			__('fields.users.deleted_at'),
		];
	}

	public function map($item): array
	{
		switch ($item->status) {
			case 1: $status = __('fields.user_status.confirmed'); break;
			case 2: $status = __('fields.user_status.not_confirmed'); break;
			default: $status = '';
		}

		return [
			$item->id,
			$item->username,
			$item->firstname,
			$item->lastname,
			$item->role,
			$item->birthdate,
			$item->email,
			$item->phone,
			$item->address,
			$item->cp,
			$item->city,
			$item->province,
			$item->country,
			$item->document_number,
			$item->lang,
			$item->confirmed ? '1' : '',
			$status,
			$item->notification_news,
			$item->notification_auctions,
			$item->notification_favorites,
			$item->number_login,
			$item->created_at,
			$item->updated_at,
			$item->deleted_at,
		];
	}

	public function columnFormats(): array
	{
		return [
			'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'U' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'V' => NumberFormat::FORMAT_DATE_DDMMYYYY,
			'W' => NumberFormat::FORMAT_DATE_DDMMYYYY,
		];
	}

	public function columnWidths(): array
	{
		return [
			'A' => \App\Models\Export::SIZE_ID,
			'B' => \App\Models\Export::SIZE_NAME,
			'C' => \App\Models\Export::SIZE_NAME,
			'D' => \App\Models\Export::SIZE_SURNAME,
			'E' => \App\Models\Export::SIZE_NAME,
			'F' => \App\Models\Export::SIZE_DATE,
			'G' => \App\Models\Export::SIZE_COMPLETE_NAME,
			'H' => \App\Models\Export::SIZE_ID,
			'I' => \App\Models\Export::SIZE_ADDRESS,
			'J' => \App\Models\Export::SIZE_ZIPCODE,
			'K' => \App\Models\Export::SIZE_LOCALITY,
			'L' => \App\Models\Export::SIZE_PROVINCE,
			'M' => \App\Models\Export::SIZE_COUNTRY,
			'N' => \App\Models\Export::SIZE_ID,
			'O' => \App\Models\Export::SIZE_NORMAL,
			'P' => \App\Models\Export::SIZE_BOOL,
			'Q' => \App\Models\Export::SIZE_NORMAL,
			'R' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'S' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'T' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'U' => \App\Models\Export::SIZE_NUMBER_SHORT,
			'V' => \App\Models\Export::SIZE_DATE,
			'W' => \App\Models\Export::SIZE_DATE,
			'X' => \App\Models\Export::SIZE_DATE,
		];
	}

	public function styles(Worksheet $sheet): array
	{
		return [
			1 => ['font' => ['bold' => true]],
		];
	}
}
