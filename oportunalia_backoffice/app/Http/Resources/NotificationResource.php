<?php

namespace App\Http\Resources;

use App\Models\Archive;
use App\Models\Notification;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $document = null;

        switch ($this->type_id)
        {
            case Notification::DOCUMENT:

                $document = (new ArchiveResource($this->whenLoaded('document')));
                break;

            case Notification::DEPOSIT:

                $archive = Archive::join('deposits', 'deposits.archive_id', '=', 'archives.id')
                    ->where('deposits.auction_id', $this->resource->auction_id)
                    ->where('deposits.user_id', $this->resource->user_id)
                    ->first();

                if ($archive) {
                    $document = ArchiveResource::make($archive)->toArray($request);
                }
                break;

            case Notification::REPRESENTATION:

                $archive = Archive::join('representations', 'representations.archive_id', '=', 'archives.id')
                    ->where('representations.id', $this->resource->representation_id)
                    ->orderBy('representations.id', 'desc')
                    ->first();

                if ($archive) {
                    $document = ArchiveResource::make($archive)->toArray($request);
                }
                break;
        }

        return array_merge(
            $this->resource->toArray(),
            [
                'document' => $document,
            ]
        );
    }
}
