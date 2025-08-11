<?php

namespace App\Models\Migration;

use Illuminate\Support\Facades\DB;

class AuctionMeta
{
	use Model;

	public static $table_name = 'wp_postmeta';

	public static $link_pattern = '/<a href=\\"([^"]*)\\"/';

	public static $fields = [
		'auction_type',
		'auction_date',
		'auction_ended',
	];

	public static $except = [
		'pageaccess',
		'_elementor_data',
		'_elementor_edit_mode',
		'_elementor_template_type',
		'_elementor_version',
		'_elementor_controls_usage',
		'_elementor_css',
		'_wp_page_template',
		'_dp_original',
		'_edit_lock',
		'_edit_last',
		'_the_champ_meta',
		'_yoast_wpseo_primary_listing',
		'_yoast_wpseo_content_score',

		'bidstring',
		'bidwinnerstring',
		'image_array',
		'current_bid_data',
		'user_maxbid_data',
	];

	public static function get($ID)
	{
		return self::query()
			// ->whereIn('meta_key', self::$fields)
			->whereNotIn('meta_key', self::$except)
			->where('post_id', $ID)
			->get();
	}

	public static function getObject($ID, $key)
	{
		return unserialize(self::query()
			->where('meta_key', $key)
			->where('post_id', $ID)
			->pluck('meta_value')
			->first()
		);
	}

	public static function getString($ID, $key)
	{
		$wp_postmeta = self::query()
			->where('meta_key', $key)
			->where('post_id', $ID)
			->pluck('meta_value')
			->first();

		$users = [];

		if ($wp_postmeta)
		{
			foreach (explode('-', $wp_postmeta) as $user)
			{
				if ($user != '') { $users[] = $user; }
			}
			return $users;
		}
		else { return []; }
	}

	public static function getJson($ID, $key)
	{
		return json_decode(self::query()
			->where('meta_key', $key)
			->where('post_id', $ID)
			->pluck('meta_value')
			->first()
		);
	}

	public static function getImages($ID) { return self::getObject($ID, 'image_array'); }
	public static function getBids($ID) { return self::getObject($ID, 'current_bid_data'); }
	public static function getBidUsers($ID) { return self::getString($ID, 'bidstring'); }
	public static function getMaxBid($ID) { return self::getObject($ID, 'user_maxbid_data'); }
	public static function getWinners($ID) { return self::getString($ID, 'bidwinnerstring'); }


	public static function findElementorBlock($block, $elType)
	{
		$found = [];

		foreach ($block->elements as $child)
		{
			if ($child->elType == $elType)
			{
				$found[] = $child;
			}
			else if (isset($child->elements) && count($child->elements))
			{
				$childFound = self::findElementorBlock($child, $elType);
				if ($childFound) { $found = array_merge($found, $childFound); }
			}
		}

		return $found;
	}

	public static function scrapeElementor($ID)
	{
		$meta = self::query()
			->where('meta_key', '_elementor_data')
			->where('post_id', $ID)
			->pluck('meta_value')
			->first();

		if (!$meta) { return null; }

		$meta = json_decode($meta);

		$address = '';
		$image = '';
		$carousel = [];
		$texts = [
			'description' => '',
			'land_registry' => '',
			'technical_specifications' => '',
			'conditions' => '',
		];

		foreach ($meta as $section)
		{
			foreach (AuctionMeta::findElementorBlock($section, 'widget') as $widget)
			{
				$settings = $widget->settings;

				switch (true)
				{
					case isset($settings->address):
						$address = $settings->address;
						break;
					case isset($settings->image):
						$image = $settings->image->url;
						break;
					case isset($settings->carousel):
						foreach ($settings->carousel as $carousel_image) {
							$carousel[] = $carousel_image->url;
						}
						break;
					case isset($settings->tabs):
						foreach ($settings->tabs as $tab) {

							switch ($tab->tab_title)
							{
								case 'Detalle': case 'Detalle listado': case 'Detalle del listado':
								case 'DESCRIPCIÓN': case 'DESCRIPCION': case 'DESCRIPCIÓN ': case 'Descripción':
								case 'LISTADO DE ACTIVOS': case 'Listado de Activos':
								case 'INVENTARIO':
								case 'DOCUMENTACIÓN': case 'DOCUMENTACIÓN DEL CONCURSO':

									$texts['description'] .= '<h2><strong>' . $tab->tab_title . '</strong></h2>';
									$texts['description'] .= $tab->tab_content;
									break;

								case 'CATASTRO':

									$texts['land_registry'] .= '<h2><strong>' . $tab->tab_title . '</strong></h2>';
									$texts['land_registry'] .= $tab->tab_content;
									break;

								case 'ESPECIFICACIONES': case 'ESPECIFICACIONES TÉCNICAS':
								case 'DOCUMENTACIÓN TÉCNICA':
								case 'PERMISO DE CIRCULACIÓN':

									$texts['technical_specifications'] .= '<h2><strong>' . $tab->tab_title . '</strong></h2>';
									$texts['technical_specifications'] .= $tab->tab_content;
									break;

								case 'CARGAS':
								case 'Detalle de los Derechos de Crédito':
								case 'CONDICIONES GENERALES':
								case 'CONDICIONES ESPECÍFICAS':
								case 'CONDICIONES PARTICULARES': case 'Condiciones Particulares': case 'Condiciones particulares':

									$texts['conditions'] .= '<h2><strong>' . $tab->tab_title . '</strong></h2>';
									$texts['conditions'] .= $tab->tab_content;
									break;

								default:

									$texts['description'] .= '<h2><strong>' . $tab->tab_title . '</strong></h2>';
									$texts['description'] .= $tab->tab_content;
							}
						}
						break;
					// default: var_dump($settings);
				}
			}
		}

		// Get all URLs from <a> tags, then download file to 'wp-documents' and
		// then replace the URLs of the files that could be downloaded for URLs
		// with current server domain in all texts.

		preg_match_all(self::$link_pattern, $texts['description'], $matches);
		foreach ($matches[1] as $url)
		{
			$new_url = migrateDocuments($url);
			$texts['description'] = str_replace($url, $new_url, $texts['description']);
		}

		preg_match_all(self::$link_pattern, $texts['land_registry'], $matches);
		foreach ($matches[1] as $url)
		{
			$new_url = migrateDocuments($url);
			$texts['land_registry'] = str_replace($url, $new_url, $texts['land_registry']);
		}

		preg_match_all(self::$link_pattern, $texts['technical_specifications'], $matches);
		foreach ($matches[1] as $url)
		{
			$new_url = migrateDocuments($url);
			$texts['technical_specifications'] = str_replace($url, $new_url, $texts['technical_specifications']);
		}

		preg_match_all(self::$link_pattern, $texts['conditions'], $matches);
		foreach ($matches[1] as $url)
		{
			$new_url = migrateDocuments($url);
			$texts['conditions'] = str_replace($url, $new_url, $texts['conditions']);
		}

		return compact([ 'address', 'image', 'carousel', 'texts' ]);
	}
}
