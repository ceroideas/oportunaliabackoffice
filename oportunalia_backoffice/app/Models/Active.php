<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Active extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $hidden=["created_at","updated_at"];

    public function auction()
    {
        return $this->hasOne(Auction::class,"active_id","id");
    }

    public function active_category()
    {
        return $this->hasOne(ActiveCategory::class,"id","active_category_id");
    }

    public function province()
    {
        return $this->hasOne(Province::class,"id","province_id");
    }

}
