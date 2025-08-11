<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ApiController;

/* Migration models */

use App\Models\Migration\ActiveCategory;
use App\Models\Migration\Auction;
use App\Models\Migration\Bid;
use App\Models\Migration\Deposit;
use App\Models\Migration\Representation;
use App\Models\Migration\User;

/* Platform models and resources */

// use App\Http\Resources\ActiveCategoryResource;
use App\Models\Role;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MigrationController extends ApiController
{
	/**
	 * Migrates users from the old database to this one.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function migrateUsers()
	{
		set_time_limit(3600);

		$wp_users = User::get();

		foreach ($wp_users as $wp_user)
		{
			\App\Models\User::create($wp_user);
		}

		$this->response = [
			'users' => $wp_users,
		];
		$this->total = count($wp_users);
		$this->code = ResponseAlias::HTTP_OK;

		return $this->sendResponse();
	}

	/**
	 * Migrates representations (granters) from the old database to this one.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function migrateRepresentations()
	{
		set_time_limit(3600);

		$wp_granters = Representation::get();

		foreach ($wp_granters as $wp_granter)
		{
			\App\Models\Representation::create($wp_granter);
		}

		$this->response = [
			'representations' => $wp_granters,
		];
		$this->total = count($wp_granters);
		$this->code = ResponseAlias::HTTP_OK;

		return $this->sendResponse();
	}

	/**
	 * Migrates asset categories from the old database to this one.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function migrateActiveCategories()
	{
		set_time_limit(3600);

		$wp_terms = ActiveCategory::get();

		foreach ($wp_terms as $wp_term)
		{
			\App\Models\ActiveCategory::create($wp_term);
		}

		$this->response = [
			'active_categories' => $wp_terms,
		];
		$this->total = count($wp_terms);
		$this->code = ResponseAlias::HTTP_OK;

		return $this->sendResponse();
	}

	/**
	 * Migrates auctions from the old database to this one.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function migrateAuctions()
	{
		set_time_limit(3600);

		$wp_posts = Auction::get();

		foreach ($wp_posts as $wp_post)
		{
			$active = \App\Models\Active::create($wp_post['active']);

			$wp_post['auction']['active_id'] = $active->id;

			$auction = \App\Models\Auction::create($wp_post['auction']);

			foreach ($wp_post['images'] as $url)
			{
				$archive_id = \App\Models\Archive::createFromUrl($url);

				if ($archive_id)
				{
					\App\Models\ActiveImages::create([
						"archive_id" => $archive_id,
						"active_id" => $active->id,
					]);
				}
			}
		}

		$this->response = [
			'auctions' => $wp_posts,
		];
		$this->total = count($wp_posts);
		$this->code = ResponseAlias::HTTP_OK;

		return $this->sendResponse();
	}

	/**
	 * Changes URL base from all URLs present in texts.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function changeUrlBase()
	{
		$urls = [
			'https://oportunalia.com',
			'https://test.oportunalia.com',
		];

		foreach ($urls as $url)
		{
			foreach (\App\Models\Auction::get() as $auction)
			{
				$auction->description = str_replace($url, url('/'), $auction->description);
				$auction->land_registry = str_replace($url, url('/'), $auction->land_registry);
				$auction->technical_specifications = str_replace($url, url('/'), $auction->technical_specifications);
				$auction->conditions = str_replace($url, url('/'), $auction->conditions);
				$auction->save();
			}
		}
	}

	/**
	 * Migrates deposits from the old database to this one.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function migrateDeposits()
	{
		set_time_limit(3600);

		$deposits = Deposit::get();

		foreach ($deposits as $deposit)
		{
			\App\Models\Deposit::create($deposit);
		}

		$this->response = [
			'deposits' => $deposits,
		];
		$this->total = count($deposits);
		$this->code = ResponseAlias::HTTP_OK;

		return $this->sendResponse();
	}

	/**
	 * Migrates deposits from the old database to this one.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function migrateBids()
	{
		set_time_limit(3600);

		$bids = Bid::get();

		foreach ($bids as $bid)
		{
			\App\Models\Bid::create($bid);
		}

		$this->response = [
			'bids' => $bids,
		];
		$this->total = count($bids);
		$this->code = ResponseAlias::HTTP_OK;

		return $this->sendResponse();
	}
}
