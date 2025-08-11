<?php

namespace App\Models\Migration;

use Illuminate\Support\Facades\DB;

class UserMeta
{
	use Model;

	public static $table_name = 'wp_usermeta';

	public static $fields = [
		'nickname',
		'first_name',
		'last_name',
		'identification_number',
		'locale',
		'wp_user_level',
		'phone',
		'address1',
		'address2',
		'zip',
		'country',
		'city',
		'birth_date',
		'login_count',
		'identification_document_reviewed',
		'wlt_verified',
		'setting_notify_disponibles',
		'setting_notify_participando',
		'setting_notify_favoritas',
		'identification_document',
	];

	public static function get($ID)
	{
		return self::query()
			->whereIn('meta_key', self::$fields)
			->where('user_id', $ID)
			->get();
	}

	public static function getObject($ID, $key)
	{
		return unserialize(self::query()
			->where('meta_key', $key)
			->where('user_id', $ID)
			->pluck('meta_value')
			->first()
		);
	}

	public static function getRoles($ID) { return self::getObject($ID, 'wp_capabilities'); }
	public static function getFavorites($ID) { return self::getObject($ID, 'favorite_list'); }
	public static function getBids($ID) { return self::getObject($ID, 'user_bidding_data'); }
	public static function getSubscription($ID) { return self::getObject($ID, 'wlt_subscription'); }
	public static function getRecent($ID) { return self::getObject($ID, 'recentlyviewed'); }
}
