<?php

namespace App\Http\Resources;

use App\Models\Auction;
use App\Models\Notification;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationAuctionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $auction = NotificationActiveResource::make(
            Auction::selectraw(
                "auctions.end_date,
                auctions.title as auction_title,
                Count(bids.id) as bids,
                Max(bids.import) as max_bid,
                auctions.active_id
            ")
            ->join("auction_statuses", "auctions.auction_status_id", "=", "auction_statuses.id")
            ->join("auction_types", "auctions.auction_type_id", "=", "auction_types.id")
            ->join("actives", "auctions.active_id", "=", "actives.id")
            ->leftJoin("bids", "auctions.id", "=", "bids.auction_id")
            ->groupBy("auctions.id")
            ->where("auctions.id", "=",  $this->resource->auction_id)
            ->first()
        );

        $newBids = Notification::where('auction_id', $this->resource->auction_id)
            ->where('status', 0)
            ->count();

        return array_merge(
            $this->resource->toArray(),
            $auction->toArray($request),
            [
                'new_bids' => $newBids,
            ]
        );
    }
}
