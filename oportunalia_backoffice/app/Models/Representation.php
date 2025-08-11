<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Representation extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ["file"];

    protected $hidden = [
        "updated_at",
        "created_at",
        "archive_id",
    ];

    public function image()
    {
        return $this->hasOne(Archive::class,"id","archive_id");
    }
}
