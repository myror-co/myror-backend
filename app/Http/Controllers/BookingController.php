<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRequest;

class BookingController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBookingPaypal(Request $request, $website_id, $listing_id)
    {
        $data = $request->validate([
            'first_name' => 'string|required|max:100',
            'last_name' => 'string|required|max:100',
            'email' => 'email|required',
            'gateway' => 'string|required|max:30',
            'status' => 'string|required|max:100',
            'reference_id' => 'string|required',
            'payment_id' => 'string|required',
            'currency' => 'string|required',
            'gross_amount' => 'numeric|required',
            'net_amount' => 'numeric|required',
            'payment_fee' => 'numeric|required',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'address_city' => 'required|string|max:100',
            'address_country' => 'required|string|max:2',
            'address_state' => 'nullable|string|max:100',
            'address_postal_code' => 'required|string|max:50',
            'paid_at' => 'required|date',
        ]);

        $website = \App\Models\Website::where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $listing = \App\Models\Listing::find($listing_id)->first();

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        //Create new booking
        $data['listing_id'] = $listing_id;
        $data['user_id'] = $website->user_id;
        $booking = \App\Models\Booking::create($data); 

        return response()->json(['message' => 'Booking successfully created'], 200); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeBookingStripe(Request $request, $website_id, $listing_id)
    {
        $data = $request->validate([
            'first_name' => 'string|required|max:100',
            'last_name' => 'string|required|max:100',
            'email' => 'email|required',
            'phone' => 'string|required',
            'guests' => 'integer|required'
        ]);

        $website = \App\Models\Website::where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $listing = \App\Models\Listing::find($listing_id)->first();

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        //Create new booking
        $data['listing_id'] = $listing_id;
        $data['user_id'] = $website->user_id;
        $data['gateway'] = 'stripe';
        $data['status'] = 'PENDING';
        $booking = \App\Models\Booking::create($data); 

        //Send mail
        // Mail::to($website->email)
        //     ->queue(new BookingRequest($listing->name, $data['first_name'], $data['last_name'], $data['guests'], $data['start'], $data['end'], $data['message'], $data['phone'], $data['email']));

        return response()->json(['message' => 'Booking successfully created'], 200); 
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function requestBooking(Request $request, $website_id, $listing_id)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'first_name' => 'string|required|max:100',
            'last_name' => 'string|required|max:100',
            'phone' => 'string|max:30',
            'guests' => 'integer|required',
            'start' => 'required|date',
            'end' => 'required|date',
            'message' => 'string|max:2000'
        ]);

        $website = \App\Models\Website::where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $listing = \App\Models\Listing::where('website_id', $website->id)->where('id', $listing_id)->first();

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        //Send mail
        Mail::to($website->email)
            ->queue(new BookingRequest($listing->name, $data['first_name'], $data['last_name'], $data['guests'], $data['start'], $data['end'], $data['message'], $data['phone'], $data['email']));

        return response()->json(['message' => 'Booking request successfully sent'], 200);
    }

    /** 
    *
    * Generate payment intent
    *
    **/
    public function getPaymentIntent(Request $request, $website_id, $listing_id)
    {
        $data = $request->validate([
            'nights' => 'integer|required',
        ]);

        $website = \App\Models\Website::where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $listing = \App\Models\Listing::where('website_id', $website->id)->where('id', $listing_id)->first();

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $payment_intent = \Stripe\PaymentIntent::create([
          'payment_method_types' => ['card'],
          'amount' => $listing->price*$data['nights']*100,
          'currency' => $listing->currency,
          'application_fee_amount' => 0,
        ], ['stripe_account' => $website->stripe_account->account_id]);

        return response()->json(['message' => 'Payment intent successfully generated', 'client_secret' => $payment_intent->client_secret], 200);
    }
}
