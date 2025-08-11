<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
    ];

    public function document()
    {
        return $this->hasOneThrough(Archive::class, User::class, "id", "id", "user_id", "archive_id");
    }

    const REGISTER = 1;
    const DOCUMENT = 2;
    const DEPOSIT = 3;
    const REPRESENTATION = 4;
    const BID = 5;
    const AUCTION_END_WIN = 6;
    const AUCTION_END = 7;
    const OFFER = 8;
    const DIRECT_SALE_END_WIN = 9;
    const DIRECT_SALE_END = 10;
    const CESION_END_WIN = 11;
    const CESION_END = 12;
}
