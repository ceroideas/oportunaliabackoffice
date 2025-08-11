<?php

namespace App\Models\Migration;

use App\Models\Archive;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class User
{
	use Model;

	public static $table_name = 'wp_users';

	public static $fields = [
		'ID',
		'user_login',
		'user_nicename',
		'user_email',
		'user_registered',
		'user_status',
		'display_name',
	];

	public static function get()
	{
		$wp_users = [];

		foreach (self::query()->select(self::$fields)->get() as $wp_user)
		{
			// Convert to array

			$wp_user = (array) $wp_user;

			// Get meta and store it at the same level of $wp_user

			foreach (UserMeta::get($wp_user['ID']) as $meta)
			{
				if (!isset($wp_user[$meta->meta_key]))
				{
					$wp_user[$meta->meta_key] = $meta->meta_value;
				}
			}

			// Create an object with the converted fields

			$values = [];

			$province = isset($wp_user['address2']) ?
				$province = \App\Models\Province::query()
					->where('name', 'LIKE', '%' . $wp_user['address2'] . '%')
					->first()
				: null;

			$country = isset($wp_user['country']) ?
				$country = \App\Models\Country::query()
					->where('iso', 'LIKE', '%' . $wp_user['country'] . '%')
					->first()
				: null;

			$values['role_id'] = ($wp_user['wp_user_level'] ?? 0) == 10 ? 1 : 2;
			$values['username'] = $wp_user['nickname'] ?? '';
			$values['firstname'] = $wp_user['first_name'] ?? '';
			$values['lastname'] = $wp_user['last_name'] ?? '';
			$values['email'] = $wp_user['user_email'];
			$values['password'] = -1;
			$values['birthdate'] = $wp_user['birth_date'] ?? '';
			$values['phone'] = $wp_user['phone'] ?? '';
			$values['address'] = $wp_user['address1'] ?? '';
			$values['cp'] = $wp_user['zip'] ?? '';
			$values['city'] = $wp_user['city'] ?? '';
			$values['province_id'] = $province ? $province->id : 1;
			$values['country_id'] = $country ? $country->id : 1;
			$values['document_number'] = $wp_user['identification_number'] ?? '';
			$values['lang'] = isset($wp_user['locale']) && $wp_user['locale'] != '' ? $wp_user['locale'] : 'es';
			$values['confirmed'] = ($wp_user['wlt_verified'] ?? '') == 'yes' ? 1 : 0;
			$values['status'] = ($wp_user['identification_document_reviewed'] ?? '') == 'true' ? 1 : 0;
			$values['created_at'] = $wp_user['user_registered'];
			$values['notification_news'] = $wp_user['setting_notify_disponibles'] ?? false;
			$values['notification_auctions'] = $wp_user['setting_notify_participando'] ?? false;
			$values['notification_favorites'] = $wp_user['setting_notify_favoritas'] ?? false;
			$values['number_login'] = $wp_user['login_count'] ?? 0;
			$values['archive_id'] = isset($wp_user['identification_document']) ? Archive::createFromUrl($wp_user['identification_document']) : null;
			$values['wp_id'] = $wp_user['ID'];

			$wp_users[] = $values;
		}

		return $wp_users;
	}
}
