<?php

namespace App\Models\Migration;

use App\Models\AuctionStatus;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Auction
{
	use Model;

	public static $table_name = 'wp_posts';

	public static $fields = [
		'ID',
		'post_author',
		'post_title',
		'post_name',
		'post_excerpt',
		'post_status',
		'post_date',
		'post_modified',
	];

	public static function get()
	{
		$wp_posts = [];

		$data = self::query()
			->where('post_type', 'listing_type')
			->select(self::$fields)
			->get();

		foreach ($data as $wp_post)
		{
			// Convert to array

			$wp_post = (array) $wp_post;
			// $wp_post = [ 'ID' => $wp_post->ID ];

			// Get meta and store it at the same level of $wp_post

			foreach (AuctionMeta::get($wp_post['ID']) as $meta)
			{
				$wp_post[$meta->meta_key] = $meta->meta_value;
			}

			// Get Auction's winners string

			$winners = AuctionMeta::getWinners($wp_post['ID']);

			// Check Auction's type

			$auction_type_id = null;
			switch ($wp_post['auction_type'])
			{
				case '1': case '2': $auction_type_id = 1; break;
				case '3': $auction_type_id = 2; break;
			}

			// Check if the Auction has started and/or ended

			$startDate = $wp_post['auction_date'] ?? null;
			$endDate = $wp_post['auction_ended'] ?? null;
			$today = date('Y-m-d');
			$started = false;
			$ended = false;

			if ($endDate) { $ended = $today >= $endDate; }
			if (!$ended && $startDate) { $started = $today >= $startDate; }
			else if ($ended) { $started = true; }

			// Check Auction's status

			$auction_status_id = AuctionStatus::DRAFT;

			if (!$startDate && !$endDate) {
				$auction_status_id = AuctionStatus::DRAFT;
			} else if (!$started && !$ended) {
				$auction_status_id = AuctionStatus::SOON;
			} else if ($started && !$ended) {
				$auction_status_id = AuctionStatus::ONGOING;
			} else if ($auction_type_id == 1) {
				$auction_status_id = count($winners) > 0 ? AuctionStatus::FINISHED : AuctionStatus::ARCHIVED;
			} else if ($auction_type_id == 2) {
				$auction_status_id = count($winners) > 0 ? AuctionStatus::SOLD : AuctionStatus::UNSOLD;
			}

			// Scrape Elementor JSON for additional data

			$elementor_data = AuctionMeta::scrapeElementor($wp_post['ID']);

			$wp_post['address'] = $elementor_data ? $elementor_data['address'] : '';

			$wp_post['description'] = $elementor_data ? $elementor_data['texts']['description'] : '';
			$wp_post['land_registry'] = $elementor_data ? $elementor_data['texts']['land_registry'] : '';
			$wp_post['technical_specifications'] = $elementor_data ? $elementor_data['texts']['technical_specifications'] : '';
			$wp_post['conditions'] = $elementor_data ? $elementor_data['texts']['conditions'] : '';

			// TODO-DEBUG:

			// $wp_posts[] = $wp_post;

			// Convert into fields for Active object

			$active_values = [];

			$address = $wp_post['address'] != '' ? $wp_post['address'] : ($wp_post['map-location'] ?? '');

			$province_id = 1;

			if (isset($wp_post['map-state']) && $wp_post['map-state'] != '')
			{
				$province = \App\Models\Province::query()
					->where('name', 'LIKE', '%' . $wp_post['map-state'] . '%')
					->first();
				if ($province) { $province_id = $province->id; }
			}
			else if (isset($wp_post['map-location']) && $wp_post['map-location'] != '')
			{
				$province = \App\Models\Province::query()
					->where('name', 'LIKE', '%' . $wp_post['map-location'] . '%')
					->first();
				if ($province) { $province_id = $province->id; }
			}

			$city = '';

			if (isset($wp_post['map-area']) && $wp_post['map-area'] != '')
			{
				$city = $wp_post['map-area'];
			}
			else if (isset($wp_post['map-city']) && $wp_post['map-city'] != '')
			{
				$city = $wp_post['map-city'];
			}

			$area = 0;

			if (isset($wp_post['asemar_meta_box_243'])) {
				$area = priceval($wp_post['asemar_meta_box_243']);
			}

			$active_category_id = ActiveCategory::getActiveCategoryOf($wp_post['ID']);

			$active_values['name'] = $wp_post['post_title'] ?? '';
			$active_values['active_category_id'] = $active_category_id;
			$active_values['address'] = $address;
			$active_values['city'] = $city;
			$active_values['province_id'] = $province_id;
			$active_values['refund'] = $wp_post['refunds'] ?? 0;
			$active_values['active_condition_id'] = $wp_post['condition'] ?? 1;
			$active_values['area'] = $area;
			$active_values['created_at'] = $wp_post['post_date'];

			$values['active'] = $active_values;

			// Convert into fields for Auction object

			$auction_values = [];

			$appraisal_value = 0;
			if (isset($wp_post['asemar_meta_box_244'])) {
				$appraisal_value = priceval($wp_post['asemar_meta_box_244']);
			}

			$start_price = 0;
			if (isset($wp_post['asemar_meta_box_245'])) {
				$start_price = priceval($wp_post['asemar_meta_box_245']);
			}

			$commission = 0;
			if (isset($wp_post['asemar_meta_box_238'])) {
				$commission = priceval($wp_post['asemar_meta_box_238']);
			}

			$auction_values['guid'] = (string) Str::uuid();
			$auction_values['active_id'] = null;
			$auction_values['auction_type_id'] = $auction_type_id;
			$auction_values['auction_status_id'] = $auction_status_id;
			$auction_values['title'] = $wp_post['post_title'] ?? '';
			$auction_values['start_date'] = $startDate ? $startDate : $wp_post['post_date'];
			$auction_values['end_date'] = $endDate ? $endDate : $auction_values['start_date'];
			$auction_values['appraisal_value'] = $appraisal_value;
			$auction_values['start_price'] = $start_price;
			$auction_values['minimum_bid'] = $wp_post['price_starting'] ?? null;
			$auction_values['deposit'] = $wp_post['asemar_minimum_deposit'] ?? null;
			$auction_values['commission'] = $commission;
			$auction_values['bid_price_interval'] = floatval($wp_post['asemar_increment_price'] ?? null);
			$auction_values['bid_time_interval'] = 60*2;
			$auction_values['description'] = $wp_post['description'];
			$auction_values['land_registry'] = $wp_post['land_registry'];
			$auction_values['technical_specifications'] = $wp_post['technical_specifications'];
			$auction_values['conditions'] = $wp_post['conditions'];
			$auction_values['sold_at'] = $auction_type_id == 2 ? $endDate : null;
			$auction_values['views'] = $wp_post['hits'];
			$auction_values['featured'] = ($wp_post['featured'] ?? '') == 'yes' ? 1 : 0;
			$auction_values['created_at'] = $wp_post['post_date'];
			$auction_values['wp_id'] = $wp_post['ID'];

			$values['auction'] = $auction_values;

			// Images from the carousel (they need to be stored after asset creation)

			$values['images'] = $elementor_data ? $elementor_data['carousel'] : [];

			$wp_posts[] = $values;
		}

		return $wp_posts;
	}
}
