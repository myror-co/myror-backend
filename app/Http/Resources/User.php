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
            'subscription' => [
                'created_at' => $this->subscribed('default') ? $this->subscription('default')->created_at->toFormattedDateString() : null,
                'ends_at' => $this->subscribed('default') && $this->subscription('default')->onGracePeriod() ? $this->subscription('default')->ends_at->toFormattedDateString() : null,
                'verification_url' => $this->hasIncompletePayment('default') ? env('API_URL').'/stripe/payment/'.$this->subscription('default')->latestPayment()->id.'?redirect='.env('APP_URL').'/billing' : null,
                'next_payment' => $this->subscribed('default') ? $this->subscription('default')->created_at->addMonths(1+now()->diffInMonths($this->subscription('default')->created_at))->toFormattedDateString() : null,
                'grace_period' => $this->subscribed('default') && $this->subscription('default')->onGracePeriod(),
                'default_card' => $this->hasDefaultPaymentMethod() ? [[
                         'id' => $this->defaultPaymentMethod()->id,
                        'brand' => ucfirst($this->defaultPaymentMethod()->card->brand),
                        'last4' => $this->defaultPaymentMethod()->card->last4,
                        'exp_month' => $this->defaultPaymentMethod()->card->exp_month,
                        'exp_year' => $this->defaultPaymentMethod()->card->exp_year
                ]] : null,
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
