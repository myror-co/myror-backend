<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
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
            'address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->address_city,
                'state' => $this->address_state,
                'country' => $this->address_country,
                'postal_code' => $this->address_postal_code
            ],
            'websites' => $this->websites->count(),
            'subscription' => [
                'created_at' => $this->subscription('default')->created_at->toFormattedDateString(),
                'ends_at' => $this->subscription('default')->onGracePeriod() ? $this->subscription('default')->ends_at->toFormattedDateString() : null,
                'next_payment' => $this->subscription('default')->created_at->addMonths(1+now()->diffInMonths($this->subscription('default')->created_at))->toFormattedDateString(),
                'subscribed' => $this->subscribed('default'),
                'grace_period' => $this->subscription('default')->onGracePeriod(),
                'default_card' => [[
                         'id' => $this->defaultPaymentMethod()->id,
                        'brand' => ucfirst($this->defaultPaymentMethod()->card->brand),
                        'last4' => $this->defaultPaymentMethod()->card->last4,
                        'exp_month' => $this->defaultPaymentMethod()->card->exp_month,
                        'exp_year' => $this->defaultPaymentMethod()->card->exp_year
                ]],
                'all_cards' => $this->paymentMethods()->map(function($card) {
                    return [
                        'id' => $card->id,
                        'brand' => ucfirst($card->card->brand),
                        'last4' => $card->card->last4,
                        'exp_month' => $card->card->exp_month,
                        'exp_year' => $card->card->exp_year
                    ];
                }),
                'invoices' => $this->invoices()->map(function($invoice) {
                    return [
                        'id' => $invoice->id,
                        'month' => $invoice->date()->format('F Y'),
                        'date_paid' => $invoice->date()->toFormattedDateString(),
                        'total' => $invoice->total(),
                        'status' => $invoice->status,
                        'download' => $invoice->invoice_pdf,
                    ];
                }),
            ],
            'created_at' => $this->created_at->toFormattedDateString(),
            'last_update' => $this->updated_at->diffForHumans(),
        ];
    }
}
