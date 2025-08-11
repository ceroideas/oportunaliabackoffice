<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class AuctionResource extends JsonResource
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
            [
                'technical_archive'=>(new ArchiveResource($this->whenLoaded('technicalArchive'))),
                'description_archive'=>(new ArchiveResource($this->whenLoaded('descriptionArchive'))),
                'land_registry_archive'=>(new ArchiveResource($this->whenLoaded('landRegistryArchive'))),
                'conditions_archive'=>(new ArchiveResource($this->whenLoaded('conditionsArchive'))),
                'technical_archive_two'=>(new ArchiveResource($this->whenLoaded('technicalArchiveTwo'))),
                'description_archive_two'=>(new ArchiveResource($this->whenLoaded('descriptionArchiveTwo'))),
                'land_registry_archive_two'=>(new ArchiveResource($this->whenLoaded('landRegistryArchiveTwo'))),
                'conditions_archive_two'=>(new ArchiveResource($this->whenLoaded('conditionsArchiveTwo'))),
                ]);
    }
}
