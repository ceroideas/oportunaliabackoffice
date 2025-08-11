<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    public function representation()
    {
        return $this->hasOne(Representation::class, "id", "representation_id");
    }

    public function parseForEmail()
    {
        $this->import = number_format($this->import, 2, ",", ".");
        $this->date = date('d/m/Y', strtotime($this->created_at));
        $this->time = date('H:i', strtotime($this->created_at));
        $this->type = __('emails.__product.bid_type.' . ($this->auto ? 'auto' : 'manual'));

        $representation = \App\Models\Representation::where("user_id", $this->user_id)
            ->first();
        $this->representation = $representation ? $representation->firstname . ' ' . $representation->lastname : __('emails.__product.noRepresentation');
    }
}
