<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionStatus extends Model
{
    use HasFactory;
    public $timestamps=false;

    const DRAFT = 2;
    const SOON = 7;
    const ONGOING = 1;
    const FINISHED = 3;
    const ARCHIVED = 4;
    const SOLD = 5;
    const UNSOLD = 6;
}
