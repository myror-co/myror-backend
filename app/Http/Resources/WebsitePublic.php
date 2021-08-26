<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ListingPublic as ListingResource;

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
        $user = \App\Models\User::where('id', $this->user_id)->first();

        return [
            'id' => $this->api_id,
            'name' => $this->name,
            'title' => $this->title,
            'icon' => $this->icon ? 'https://'.env('AWS_BUCKET').'.s3.amazonaws.com/'.$this->icon : $this->icon,
            'description' => $this->description ?? "",
            'meta_description' => $this->meta_description ?? "",
            'cancellation_policy' => $this->cancellation_policy ?? "",
            'no_show_policy' => $this->no_show_policy ?? "",
            'deposit_policy' => $this->deposit_policy ?? "",
            'other_policy' => $this->other_policy ?? "",
            'main_picture' => $this->main_picture,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp_number' => $this->whatsapp_number,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'google' => $this->google,
            'paypal_client_id' => $user->subscribed('default') && $this->paypal_client_id,
            'branding' => !$user->subscribed('default'),
            'csrf' => csrf_token(),
            'listings' => ListingResource::collection($this->listings)
        ];
    }
}
