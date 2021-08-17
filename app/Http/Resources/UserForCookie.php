<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserForCookie extends JsonResource
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
            'email' => $this->email, 
            'avatar' => $this->avatar,
            'address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->address_city,
                'state' => $this->address_state,
                'country' => $this->address_country,
                'postal_code' => $this->address_postal_code
            ],
            'websites' => $this->websites->count(),
            'subscribed' => $this->subscribed('default'),
            'incomplete' => $this->hasIncompletePayment('default'),
            'created_at' => $this->created_at->toFormattedDateString(),
            'last_update' => $this->updated_at->diffForHumans(),
        ];
    }
}
