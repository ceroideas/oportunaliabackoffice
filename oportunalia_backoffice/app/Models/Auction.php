<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $hidden=["deleted_at","updated_at","technical_archive_id","description_archive_id","land_registry_archive_id","conditions_archive_id","technical_archive_two_id","description_archive_two_id","land_registry_archive_two_id","conditions_archive_two_id"];
    protected $casts = [
        'created_at' => "datetime:Y-m-d H:i:s",
    ];

    public function active()
    {
        return $this->belongsTo(Auction::class,"id","active_id");
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class,"auction_id","id");
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class,"auction_id","id");
    }

    public function bids()
    {
        return $this->hasMany(Bid::class,"auction_id","id");
    }

    public function last_bid()
    {
        return $this->hasOne(Bid::class,"auction_id","id")
            ->with(['user' => function($query) { $query->select('id', 'username'); }])
            ->orderBy('created_at', 'desc');
    }

    public function offers()
    {
        return $this->hasMany(DirectSaleOffer::class,"auction_id","id");
    }

    public function last_offer()
    {
        return $this->hasOne(DirectSaleOffer::class,"auction_id","id")
            ->with(['user' => function($query) { $query->select('id', 'username'); }])
            ->orderBy('id', 'desc');
    }

    public function best_offer()
    {
        return $this->hasOne(DirectSaleOffer::class,"auction_id","id")
            ->with(['user' => function($query) { $query->select('id', 'username'); }])
            ->orderBy('import', 'desc');
    }

    public function technicalArchive()
    {
        return $this->hasOne(Archive::class,"id","technical_archive_id");
    }
    public function descriptionArchive()
    {
        return $this->hasOne(Archive::class,"id","description_archive_id");
    }
    public function landRegistryArchive()
    {
        return $this->hasOne(Archive::class,"id","land_registry_archive_id");
    }
    public function conditionsArchive()
    {
        return $this->hasOne(Archive::class,"id","conditions_archive_id");
    }

    public function technicalArchiveTwo()
    {
        return $this->hasOne(Archive::class,"id","technical_archive_two_id");
    }
    public function descriptionArchiveTwo()
    {
        return $this->hasOne(Archive::class,"id","description_archive_two_id");
    }
    public function landRegistryArchiveTwo()
    {
        return $this->hasOne(Archive::class,"id","land_registry_archive_two_id");
    }
    public function conditionsArchiveTwo()
    {
        return $this->hasOne(Archive::class,"id","conditions_archive_two_id");
    }

    public function parseForEmail()
    {
        $this->startPrice = number_format($this->start_price, 2, ",", ".");
        $this->appraisalValue = number_format($this->appraisal_value, 2, ",", ".");

        if ($this->auction_type_id == 1)
        {
            $bids = \App\Models\Bid::where("auction_id", $this->id)
                ->orderBy("bids.import", "desc")
                ->get();

            $countBids = $bids->count();
            $lastBid = $countBids ? $bids[0]->import : 0;
            $this->lastBid = number_format($lastBid, 2, ",", ".");
            $this->lastBidder = $countBids ? substr($bids[0]->user->firstname, 0, 3).'*' : '';
            $this->total_bids = $countBids;
            $this->reached = $lastBid >= $this->start_price;
            $this->commission_import = number_format($lastBid * ($this->commission / 100), 2, ",", ".");
            $this->total = number_format($lastBid * (1 + ($this->commission / 100)), 2, ",", ".");
        }
        else if ($this->auction_type_id == 2)
        {
            $offers = \App\Models\DirectSaleOffer::where("auction_id", $this->id)
                ->orderBy("direct_sale_offers.import", "desc")
                ->get();

            $countOffers = $offers->count();
            $lastOffer = $countOffers ? $offers[0]->import : 0;
            $this->lastOffer = number_format($lastOffer, 2, ",", ".");
            $this->lastOfferer = $countOffers ? substr($offers[0]->user->firstname, 0, 3).'*' : '';
            $this->total_offers = $countOffers;
            $this->commission_import = number_format($lastOffer * ($this->commission / 100), 2, ",", ".");
            $this->total = number_format($lastOffer * (1 + ($this->commission / 100)), 2, ",", ".");
        }

        $images = \App\Http\Resources\ActiveImagesResource::collection(
            ActiveImages::with('image')
                ->where("active_images.active_id", "=", $this->active_id)
                ->get()
        )
        ->toArray(null);
        $this->image = count($images) ? $images[0]['path'] : '';

        $this->path = $this->auction_type_id == 1 ? url('/subasta/'.$this->link_rewrite) : url('/subasta/'.$this->link_rewrite);
    }

    public function activo(){
        return $this->hasOne(Active::class,"id","active_id");
    }
}
