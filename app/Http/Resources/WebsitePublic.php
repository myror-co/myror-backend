<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Listing as ListingResource;

class WebsitePublic extends JsonResource
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
            'title' => $this->title,
            'icon' => $this->icon ? 'https://'.env('AWS_BUCKET').'.s3.amazonaws.com/'.$this->icon : $this->icon,
            'description' => $this->description,
            'meta_description' => $this->meta_description,
            'main_picture' => $this->main_picture,
            'email' => $this->email,
            'phone' => $this->phone,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'google' => $this->google,
            'csrf' => csrf_token(),
            'listings' => ListingResource::collection($this->listings)
        ];
    }
}
