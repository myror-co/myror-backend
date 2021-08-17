<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Booking extends JsonResource
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
            'payment_id' => $this->payment_id,
            'reference_id' => $this->reference_id,
            'receipt_url' => $this->receipt_url,
            'gateway' => $this->gateway,
            'listing' => $this->listing,
            'name' => $this->first_name.' '.$this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'checkin' => $this->checkin ? \Carbon\Carbon::parse($this->checkin)->toFormattedDateString() : '',
            'checkout' => $this->checkout ? \Carbon\Carbon::parse($this->checkout)->toFormattedDateString() : '',
            'guests' => $this->guests,
            'currency' => $this->currency,
            'amount' => $this->gross_amount,
            'paid_at' => $this->paid_at ? \Carbon\Carbon::parse($this->paid_at)->toFormattedDateString() : '',
        ];
    }
}
