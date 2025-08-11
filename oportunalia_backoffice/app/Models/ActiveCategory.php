<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActiveCategory extends Model
{
    use HasFactory,SoftDeletes;
    public $timestamps = false;
    protected $guarded=[];
    protected $hidden=["deleted_at"];

    public function image()
    {
        return $this->hasOne(Archive::class,"id","archive_id");
    }

    public function actives()
    {
        return $this->hasMany(Active::class,"active_category_id","id");
    }
}
