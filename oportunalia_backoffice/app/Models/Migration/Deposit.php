<?php

namespace App\Models\Migration;

use App\Models\Archive;
use App\Models\Auction;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Deposit
{
	use Model;

	public static $table_name = 'wp_asemar_deposit_documents';

	public static $fields = [
		'id',
		'user_id',
		'auction_id',
		'upload_date',
		'document_url',
		'active',
	];

	public static function get()
	{
		$deposits = [];

		foreach (self::query()->select(self::$fields)->get() as $deposit)
		{
			// Convert to array

			$deposit = (array) $deposit;

			// TODO-DEBUG:

			// $deposits[] = $deposit;

			// Create an object with the converted fields

			$values = [];

			$auction = Auction::where('wp_id', $deposit['auction_id'])->first();
			$import = $auction->deposit ?? 0;

			$user = User::where('wp_id', $deposit['user_id'])->first();

			$values['user_id'] = $user->id;
			$values['auction_id'] = $auction->id;
			$values['status'] = $deposit['active'];
			$values['deposit'] = $import;
			$values['created_at'] = $deposit['upload_date'];
			$values['archive_id'] = Archive::createFromUrl($deposit['document_url']);
			$values['wp_id'] = $deposit['id'];

			$deposits[] = $values;
		}

		return $deposits;
	}
}
