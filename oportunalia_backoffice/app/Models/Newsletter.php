<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $guarded = ["auctions"];
    protected $hidden = ["updated_at"];
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
    ];

    public function template()
    {
        return $this->hasOne(NewsletterTemplate::class,"id","template_id");
    }
}
