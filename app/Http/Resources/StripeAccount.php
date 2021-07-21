<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StripeAccount extends JsonResource
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
            'account_id' => $this->account_id,
            'email' => $this->email,
            'setup_url' => $this->setup_url,
            'charges_enabled' => $this->charges_enabled,
            'details_submitted' => $this->details_submitted
        ];
    }
}
