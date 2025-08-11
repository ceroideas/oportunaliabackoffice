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

use Carbon\Carbon;

use DB;

class UserInterestsExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
	public function __construct()
	{
		$this->provinces = DB::table('provinces')->get();
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
			'Viviendas',
			'Naves Industriales',
			'Garajes',
			'Trasteros',
			'Locales',
			'Lotes de Mobiliario',
			'Aplicaciones Informaticas',
			'Derechos Cobro Creditos',
			'Maquinaria',
			'Solares',
			'Vehiculos',
			'Obras de Arte y Antiguedades',
			'Unidades Productivas',
			'Oficinas',
			'Rusticas',
			'Otros',

			'Ubicación',
			'Prefieres propiedades para',
			'Presupuesto minimo',
			'Presupuesto maximo',
			'Tipo Activo',
		];
	}

	public function map($item): array
	{
		switch ($item->status) {
			case 1: $status = __('fields.user_status.confirmed'); break;
			case 2: $status = __('fields.user_status.not_confirmed'); break;
			default: $status = '';
		}

		$names = [];

		$activo = "";

		if (isset($item->interests['ubicacion'])) {
			$result = $this->provinces->whereIn('id', $item->interests['ubicacion']);
			if ($result) {
				foreach ($result as $key => $v) {
					$names[] = $v->name;
				}
			}
		}

		if (isset($item->interests['activos'])) {
			if ($item->interests['activos'] == 0) {
				$activo = "Todos";
			}
			if ($item->interests['activos'] == 1) {
				$activo = "Subasta";
			}
			if ($item->interests['activos'] == 2) {
				$activo = "Venta Directa";
			}
			if ($item->interests['activos'] == 3) {
				$activo = "Cesión de remate";
			}
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
			isset($item->interests['viviendas']) ? $item->interests['viviendas'] ? 'Si' : '' : '',
			isset($item->interests['naves_industriales']) ? $item->interests['naves_industriales'] ? 'Si' : '' : '',
			isset($item->interests['garajes']) ? $item->interests['garajes'] ? 'Si' : '' : '',
			isset($item->interests['trasteros']) ? $item->interests['trasteros'] ? 'Si' : '' : '',
			isset($item->interests['locales']) ? $item->interests['locales'] ? 'Si' : '' : '',
			isset($item->interests['lotes_mobiliario']) ? $item->interests['lotes_mobiliario'] ? 'Si' : '' : '',
			isset($item->interests['aplicaciones_informaticas']) ? $item->interests['aplicaciones_informaticas'] ? 'Si' : '' : '',
			isset($item->interests['derechos_cobro_creditos']) ? $item->interests['derechos_cobro_creditos'] ? 'Si' : '' : '',
			isset($item->interests['maquinaria']) ? $item->interests['maquinaria'] ? 'Si' : '' : '',
			isset($item->interests['solares']) ? $item->interests['solares'] ? 'Si' : '' : '',
			isset($item->interests['vehiculos']) ? $item->interests['vehiculos'] ? 'Si' : '' : '',
			isset($item->interests['obras_arte_antiguedades']) ? $item->interests['obras_arte_antiguedades'] ? 'Si' : '' : '',
			isset($item->interests['unidades_productivas']) ? $item->interests['unidades_productivas'] ? 'Si' : '' : '',
			isset($item->interests['oficinas']) ? $item->interests['oficinas'] ? 'Si' : '' : '',
			isset($item->interests['rusticas']) ? $item->interests['rusticas'] ? 'Si' : '' : '',
			isset($item->interests['otros']) ? $item->interests['otros'] ? 'Si' : '' : '',
			isset($item->interests['ubicacion']) ? implode(', ', $names) : '',
			isset($item->interests['inversion']) ? ($item->interests['inversion'] == 0 ? 'Inversión' : 'Residencia Propia') : '',
			isset($item->interests['presupuesto']) ? $item->interests['presupuesto'] : '',
			isset($item->interests['presupuesto1']) ? $item->interests['presupuesto1'] : '',
			$activo,
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
