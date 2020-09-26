<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Listing as ListingResource;

class Website extends JsonResource
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
            'id' => $this->api_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'icon' => $this->icon,
            'description' => $this->description,
            'main_picture' => $this->main_picture,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'google' => $this->google,
            'listings' => ListingResource::collection($this->listings),
            'created_at' => $this->created_at->toFormattedDateString(),
            'last_update' => $this->updated_at->diffForHumans(),
        ];
    }
}
