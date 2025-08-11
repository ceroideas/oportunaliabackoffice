<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    const ID_ADMIN = 1;
    const ID_USER = 2;
    const ID_ADMIN_CONTEST = 3;
    const ID_USER_COMMERCIAL = 4;
    const ID_ACREEDOR = 5;
}
