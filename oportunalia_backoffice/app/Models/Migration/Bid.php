<?php

namespace App\Models\Migration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Bid
{
	use Model;

	public static $table_name = 'wp_posts';

	public static $fields = [
		'ID',
	];

	public static function get()
	{
		$bids = [];

		$data = self::query()
			->where('post_type', 'listing_type')
			->select(self::$fields)
			->get();

		foreach ($data as $wp_post)
		{
			// Convert to array

			$wp_post = (array) $wp_post;

			// Get bids data for every auction

			$auction_bids = AuctionMeta::getBids($wp_post['ID']);

			// TODO-DEBUG:

			// $wp_posts[] = $wp_post;

			// Iterate all bids and convert into fields for Bid object

			if ($auction_bids)
			{
				foreach (array_keys($auction_bids) as $key)
				{

					$values = [];

					$user = \App\Models\User::where('wp_id', $auction_bids[$key]['userid'])->first();
					$auction = \App\Models\Auction::where('wp_id', $wp_post['ID'])->first();

					if ($user && $auction)
					{
						$representation = null;
						if (isset($auction_bids[$key]['granterid'])) {
							$representation = \App\Models\Representation::where('wp_id', $auction_bids[$key]['granterid'])->first();
						}

						$values['user_id'] = $user->id;
						$values['auction_id'] = $auction->id;
						$values['representation_id'] = $representation ? $representation->id : null;
						$values['import'] = $auction_bids[$key]['amount'];
						$values['created_at'] = $auction_bids[$key]['date'];

						$bids[] = $values;
					}
				}
			}
		}

		return $bids;
	}
}
