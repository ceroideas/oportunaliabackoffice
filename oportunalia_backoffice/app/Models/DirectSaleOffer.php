<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectSaleOffer extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $hidden=["updated_at"];
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
    ];

    public function auction()
    {
        return $this->hasOne(Auction::class, "id", "auction_id");
    }

    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function parseForEmail()
    {
        $this->import = number_format($this->import, 2, ",", ".");
        $this->date = date('d/m/Y', strtotime($this->created_at));
        $this->time = date('H:i', strtotime($this->created_at));

        $representation = \App\Models\Representation::where("user_id", $this->user_id)
            ->first();
        $this->representation = $representation ? $representation->firstname . ' ' . $representation->lastname : __('emails.__product.noRepresentation');
    }
}
