<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Log;

class StripeWebhookController extends CashierController
{

    /**
     * Handle payment intent succeeded
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handlePaymentIntentSucceeded($payload)
    {
        //Get booking
        $booking = \App\Models\Booking::where('client_secret', $payload['data']['object']['client_secret'])
                                        ->where('email', $payload['data']['object']['charges']['data'][0]['billing_details']['email'])
                                        ->first();

        if(!$booking)
        {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        $data = array(
            'receipt_url' => $payload['data']['object']['charges']['data'][0]['receipt_url'],
            'currency' => $payload['data']['object']['charges']['data'][0]['currency'],
            'gross_amount' => $payload['data']['object']['charges']['data'][0]['amount']/100,
            'net_amount' => $payload['data']['object']['charges']['data'][0]['amount']/100,
            'payment_fee' => 0,
            'status' => 'COMPLETED',
            'reference_id' => $payload['data']['object']['id'],
            'payment_id' => $payload['data']['object']['charges']['data'][0]['id'],
            'paid_at' => $payload['data']['object']['charges']['data'][0]['created'],
        );

        $booking->update($data);

        //Send mail
        // Mail::to($website->email)
        //     ->queue(new BookingRequest($listing->name, $data['first_name'], $data['last_name'], $data['guests'], $data['start'], $data['end'], $data['message'], $data['phone'], $data['email']));

        return response()->json(['message' => 'Booking updated'], 200);
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