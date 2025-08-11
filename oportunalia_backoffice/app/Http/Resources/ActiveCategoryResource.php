<?php

namespace App\Http\Resources;

use App\Models\Archive;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image'=>(new ArchiveResource($this->whenLoaded('image')))
        ];
    }
}
