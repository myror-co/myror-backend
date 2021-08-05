<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Log;

class WebhookController extends CashierController
{

    /**
     * Handle payment intent succeeded
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handlePaymentIntentSucceeded($payload)
    {
        Log::debug($payload);

        $data = array(
            'user_id' => 17,
            'listing_id' => 71,
            'guests' => 1,
            'name' => $payload['data']['object']['charges']['data'][0]['billing_details']['name'] ? $payload['data']['object']['charges']['data'][0]['billing_details']['name']:  'test',
            'email' => $payload['data']['object']['charges']['data'][0]['billing_details']['email'] ? $payload['data']['object']['charges']['data'][0]['billing_details']['email']: 'test',
            'phone' => $payload['data']['object']['charges']['data'][0]['billing_details']['phone'] ?? 'test',
            'receipt_url' => $payload['data']['object']['charges']['data'][0]['receipt_url'],
            'payment_id' => $payload['data']['object']['charges']['data'][0]['id'],
            'currency' => $payload['data']['object']['charges']['data'][0]['currency'],
            'gross_amount' => $payload['data']['object']['charges']['data'][0]['amount']/100,
            'net_amount' => $payload['data']['object']['charges']['data'][0]['amount']/100,
            'payment_fee' => 0,
            'gateway' => 'stripe',
            'status' => 'COMPLETED',
            'reference_id' => $payload['data']['object']['id'],
            'payment_id' => $payload['data']['object']['charges']['data'][0]['id'],
            'paid_at' => $payload['data']['object']['charges']['data'][0]['created'],
        );

        $booking = \App\Models\Booking::create($data);

        //Send mail
        // Mail::to($website->email)
        //     ->queue(new BookingRequest($listing->name, $data['first_name'], $data['last_name'], $data['guests'], $data['start'], $data['end'], $data['message'], $data['phone'], $data['email']));

        Log::debug('An informational message.');
    }

    /**
     * Handle invoice payment succeeded.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleInvoicePaymentSucceeded($payload)
    {
        // Handle the incoming event...
    }
}