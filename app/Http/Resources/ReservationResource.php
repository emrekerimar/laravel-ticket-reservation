<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'reserved_at'  => $this->reserved_at?->toDateTimeString(),
            'expires_at'   => $this->expires_at?->toDateTimeString(),
            'event'        => new EventResource($this->whenLoaded('event')),
        ];
    }
}
