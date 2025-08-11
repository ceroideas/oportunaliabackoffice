<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class RepresentationResource extends JsonResource
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
            Arr::except($this->resource->toArray(), ['image']),
            [
                'document' => (new ArchiveResource($this->whenLoaded('image')))
            ]
        );
    }
}
