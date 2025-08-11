<?php

namespace App\Http\Resources;

use App\Models\ActiveImages;
use App\Models\Favorite;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AuctionResourceWeb extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge(
            $this->resource->toArray(),
            Auth::id() ? [
                "favorite" => Favorite::where("favorites.auction_id","=",$this->id)
                    ->where("favorites.user_id","=",  Auth::id())
                    ->first() ? 1 : 0
            ] : [ "favorite" => null ],
            [
                'images' => ActiveImagesResource::collection(
                    ActiveImages::with('image')
                        ->where("active_images.active_id", "=", $this->active_id)
                        ->get()
                ),
                'technical_archive'=>(new ArchiveResource($this->whenLoaded('technicalArchive'))),
                'description_archive'=>(new ArchiveResource($this->whenLoaded('descriptionArchive'))),
                'land_registry_archive'=>(new ArchiveResource($this->whenLoaded('landRegistryArchive'))),
                'conditions_archive'=>(new ArchiveResource($this->whenLoaded('conditionsArchive'))),

            ],
        );
    }
}
