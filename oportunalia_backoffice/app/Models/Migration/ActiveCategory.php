<?php

namespace App\Models\Migration;

use App\Models\Archive;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActiveCategory
{
	use Model;

	public static $table_name = 'wp_terms';

	// WARNING: this might change. Check it after migrating ActiveCategories.
	public static $fallback_active_category_id = 41;

	public static $fields = [
		'wp_terms.term_id as id',
		'wp_terms.name',
		'wp_term_taxonomy.term_taxonomy_id as tt_id',
		'wp_term_taxonomy.description',
	];

	public static function get()
	{
		$wp_terms = [];

		$data = self::query()
			->select(self::$fields)
			->join('wp_term_taxonomy', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
			->where('taxonomy', 'listing')
			->get();

		foreach ($data as $wp_term)
		{
			// Convert to array

			$wp_term = (array) $wp_term;

			// Create an object with the converted fields

			$values = [];

			$values['name'] = $wp_term['name'];
			$values['description'] = $wp_term['description'];
			$values['wp_id'] = $wp_term['id'];

			$wp_terms[] = $values;
		}

		return $wp_terms;
	}

	public static function getActiveCategoryOf($id)
	{
		$data = self::query()
			->select(self::$fields)
			->join('wp_term_taxonomy', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
			->join('wp_term_relationships as wtr', 'wtr.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
			->where('wp_term_taxonomy.taxonomy', 'listing')
			->where('wtr.object_id', $id)
			->first();

		if ($data)
		{
			$category = \App\Models\ActiveCategory::where('wp_id', $data->id)->first();
			if ($category) { return $category->id; }
		}

		return self::$fallback_active_category_id;
	}
}
