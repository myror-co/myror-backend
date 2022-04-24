<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ListingPublic extends JsonResource
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
            'website_id' => $this->website->api_id, 
            'name' => $this->name,
            'slug' => $this->slug, 
            'picture_sm' => $this->picture_sm, 
            'picture_xl' => $this->picture_xl, 
            'pricing_type' => $this->pricing_type,
            'price' => $this->price, 
            'currency' => $this->website->currency, 
            'city'=> $this->city, 
            'country'=> $this->country, 
            'smart_location'=> $this->smart_location, 
            'lat'=> $this->lat, 
            'lng'=> $this->lng, 
            'user'=> $this->user, 
            'hosts'=> $this->hosts, 
            'bathrooms'=> $this->bathrooms, 
            'bedrooms'=> $this->bedrooms, 
            'beds'=> $this->beds, 
            'capacity'=> $this->capacity, 
            'property_type'=> $this->property_type, 
            'room_type'=> $this->room_type, 
            'summary'=> $this->description ? Str::limit($this->description, 200, '...') : "", 
            'description'=> $this->description ?? "", 
            'space'=> $this->space ?? "", 
            'neighborhood'=> $this->neighborhood ?? "", 
            'amenities'=> $this->amenities, 
            'checkin_time'=> $this->checkin_time, 
            'checkout_time'=> $this->checkout_time, 
            'minimum_nights' => $this->minimum_nights,
            'maximum_nights' => $this->maximum_nights,
            'weekly_factor' => $this->weekly_factor,
            'monthly_factor' => $this->monthly_factor,
            'additional_guest_fee' => $this->additional_guest_fee,
            'additional_guest_price' => $this->additional_guest_price,
            'additional_guest_threshold' => $this->additional_guest_threshold,
            'cleaning_fee' => $this->cleaning_fee,
            'cleaning_price' => $this->cleaning_price,
            'security_deposit_fee' => $this->security_deposit_fee,
            'security_deposit_price' => $this->security_deposit_price,
            'photos'=> $this->photos, 
            'recent_review'=> $this->recent_review, 
            'reviews_count'=> $this->reviews_count, 
            'rating'=> $this->rating, 
            'rules'=> $this->rules, 
        ];
    }
}
