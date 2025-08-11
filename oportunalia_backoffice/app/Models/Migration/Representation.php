<?php

namespace App\Models\Migration;

use App\Models\Archive;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Representation
{
	use Model;

	public static $table_name = 'wp_asemar_granters';

	public static $url_base = 'https://asemargc.com/wp-content/uploads/asemar-granters/';

	public static $fields = [
		'id',
		'user_id',
		'granter_registered',
		'granter_name',
		'granter_type',
		'nifcifid',
		'address',
		'phone',
		'certificate',
		'status',
		'_deleted',
		'country',
		'city',
		'postal_code',
		'province',
		'nombre',
		'apellidos',
	];

	public static function get()
	{
		$wp_granters = [];

		foreach (self::query()->select(self::$fields)->get() as $wp_granter)
		{
			// Convert to array

			$wp_granter = (array) $wp_granter;

			// Create an object with the converted fields

			$user = User::where('wp_id', $wp_granter['user_id'])->first();

			if ($user)
			{
				$values = [];

				$province = isset($wp_granter['province']) ?
					$province = \App\Models\Province::query()
						->where('name', 'LIKE', '%' . $wp_granter['province'] . '%')
						->first()
					: null;

				$country = isset($wp_granter['country']) ?
					$country = \App\Models\Country::query()
						->where('iso', 'LIKE', '%' . $wp_granter['country'] . '%')
						->first()
					: null;

				$archive_id = null;

				if (strlen($wp_granter['certificate']))
				{
					$filename_parts = explode('.', $wp_granter['certificate']);
					$ext = array_pop($filename_parts);
					$name = implode($filename_parts);
					$new_name = uniqid() . uniqid();

					$url = self::$url_base . $wp_granter['user_id'] . '/' . $wp_granter['certificate'];

					if (checkUrl($url))
					{
						$file = Storage::disk('public')->put($new_name . '.' . $ext, fopen($url, 'r'));

						$archive = Archive::create([
							"name" => $name . '.' . $ext,
							"path" => $new_name . '.' . $ext
						]);

						$archive_id = $archive->id;
					}
				}

				$status = 0;

				switch ($wp_granter['status'])
				{
					case 'Confirmed': $status = 1; break;
					case 'Denied': $status = 2; break;
					case 'Pending': $status = 0; break;
				}

				$values['alias'] = $wp_granter['granter_name'] ?? '';
				$values['guid'] = (string) Str::uuid();
				$values['firstname'] = $wp_granter['nombre'] ?? '';
				$values['lastname'] = $wp_granter['apellidos'] ?? '';
				$values['document_number'] = $wp_granter['nifcifid'] ?? '';
				$values['address'] = $wp_granter['address'] ?? '';
				$values['city'] = $wp_granter['city'] ?? '';
				$values['cp'] = $wp_granter['postal_code'] ?? '';
				$values['province_id'] = $province ? $province->id : 1;
				$values['country_id'] = $country ? $country->id : 1;
				$values['representation_type_id'] = $wp_granter['granter_type'] == 'Person' ? 1 : 2;
				$values['user_id'] = $user->id;
				$values['status'] = $status;
				$values['created_at'] = $wp_granter['granter_registered'];
				$values['deleted_at'] = $wp_granter['_deleted'] == 1 ? date('Y-m-d H:i:s') : null;
				$values['archive_id'] = $archive_id;
				$values['wp_id'] = $wp_granter['id'];

				$wp_granters[] = $values;
			}
		}

		return $wp_granters;
	}
}
