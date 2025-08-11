<?php

namespace App\Models\Migration;

use Illuminate\Support\Facades\DB;

trait Model
{
	public static function query()
	{
		return DB::connection('mysql_old')->table(self::$table_name);
	}
}
