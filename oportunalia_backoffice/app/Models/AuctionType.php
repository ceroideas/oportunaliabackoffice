<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionType extends Model
{
    use HasFactory;
    public $timestamps=false;

    const AUCTION = 1;
    const DIRECT_SALE = 2;
    const CESION = 3;
    const CREDIT_ASSIGNMENT = 4;
}
