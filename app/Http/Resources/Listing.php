<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Listing extends JsonResource
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
            'name' => $this->name, 
            'picture_sm' => $this->picture_sm, 
            'picture_xl' => $this->picture_xl, 
            'price' => $this->price, 
            'currency' => $this->currency, 
            'city'=> $this->city, 
            'country'=> $this->country, 
            'smart_location'=> $this->smart_location, 
            'bathrooms'=> $this->bathrooms, 
            'bedrooms'=> $this->bedrooms, 
            'beds'=> $this->beds, 
            'capacity'=> $this->capacity, 
            'property_type'=> $this->property_type, 
            'room_type'=> $this->room_type, 
            'summary'=> $this->summary, 
            'description'=> $this->description, 
            'space'=> $this->space, 
            'neighborhood'=> $this->neighborhood, 
            'amenities'=> $this->amenities, 
            'checkout_time'=> $this->checkout_time, 
            'photos'=> $this->photos, 
            'recent_review'=> $this->recent_review, 
            'created_at' => $this->created_at->toFormattedDateString(),
            'last_update' => $this->updated_at->diffForHumans(),
        ];
    }
}
