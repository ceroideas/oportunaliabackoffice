<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveImages extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function image()
    {
        return $this->hasOne(Archive::class,"id","archive_id");
    }
}
