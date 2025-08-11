<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'username',
        'firstname',
        'lastname',
        'email',
        'password',
        'birthdate',
        'phone',
        'address',
        'cp',
        'city',
        'province_id',
        'country_id',
        'document_number',
        'document_path',
        'lang',
        'confirmed',
        'status',
        'created_at',
        'notification_news',
        'notification_auctions',
        'notification_favorites',
        'archive_id',
        'archive_two_id',
        'number_login',
        'wp_id',
    ];

    protected $casts = ["interests" => "array"];

    protected $guarded = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'updated_at',
        'archive_id',
        'archive_two_id',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function document()
    {
        return $this->hasOne(Archive::class,"id","archive_id");
    }
    public function documentTwo()
    {
        return $this->hasOne(Archive::class,"id","archive_two_id");
    }
}
