<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\ApiController;
use App\Models\DirectSaleOffer;
use App\Models\Deposit;

class DepositController extends ApiController
{
	public function all()
	{
		return Deposit::all();
		$dep = Deposit::find(92);
		$dep->status = 1;
		$dep->valid = 1;
		$dep->save();
	}
}
