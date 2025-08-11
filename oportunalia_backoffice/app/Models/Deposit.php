<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function auction()
    {
        return $this->hasOne(Auction::class, "id", "auction_id");
    }

    public function document()
    {
        return $this->hasOne(Archive::class, "id", "archive_id");
    }
}
