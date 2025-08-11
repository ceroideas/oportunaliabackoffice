<?php

namespace App\Http\Resources;

use App\Models\ActiveImages;
use App\Models\Auction;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationActiveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            $this->resource->toArray(),
            [ 'images'=>ActiveImagesResource::collection(ActiveImages::with('image')->where("active_images.active_id", "=", $this->active_id)->get())]


        );
    }
}
