<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstagramPlugin extends JsonResource
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
            'instagram_user_id' => $this->instagram_user_id,
            'instagram_username' => $this->instagram_username,
            'website_id' => $this->website_id,
            'access_token' => $this->access_token,
        ];
    }
}
