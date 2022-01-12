<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRequest;
use App\Mail\NewBooking;
use App\Http\Resources\Booking as BookingResource;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = Auth::user()->bookings()->where('status', 'CONFIRMED')->get();

        return BookingResource::collection($bookings);
    }

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
            'phone' => 'nullable|string',
            'guests' => 'integer|required',
            'checkin' => 'required|date',
            'checkout' => 'required|date',
            'reference_id' => 'string|required',
            'payment_id' => 'string|required',
            'currency' => 'string|required',
            'gross_amount' => 'numeric|required',
            'net_amount' => 'numeric|required',
            'payment_fee' => 'nullable|numeric',
            'address_line1' => 'nullable|string',
            'address_line2' => 'nullable|string',
            'address_city' => 'nullable|string',
            'address_country' => 'nullable|string',
            'address_state' => 'nullable|string',
            'address_postal_code' => 'string',
            'paid_at' => 'required|date',
        ]);

        $website = \App\Models\Website::where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $listing = \App\Models\Listing::find($listing_id);

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        //Create new booking
        $data['uuid'] =Str::uuid();
        $data['listing_id'] = $listing_id;
        $data['user_id'] = $website->user_id;
        $data['gateway'] = 'paypal';
        $data['status'] = 'CONFIRMED';
        $data['checkin'] = \Carbon\Carbon::createFromFormat('Y-m-d', $data['checkin'], $listing->timezone_name);
        $data['checkout'] = \Carbon\Carbon::createFromFormat('Y-m-d', $data['checkout'], $listing->timezone_name);
        $booking = \App\Models\Booking::create($data); 

        //Store booking in iCal calendar

        //Send mail new booking 
        Mail::to($website->email)->queue(new NewBooking($booking));

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
            'client_secret' => 'string|required',
            'first_name' => 'string|required|max:100',
            'last_name' => 'string|required|max:100',
            'email' => 'email|required',
            'phone' => 'string|required',
            'guests' => 'integer|required',
            'checkin' => 'required|date',
            'checkout' => 'required|date',
        ]);

        $website = \App\Models\Website::where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $listing = \App\Models\Listing::find($listing_id);

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        //Create new booking
        $data['uuid'] =Str::uuid();
        $data['listing_id'] = $listing_id;
        $data['user_id'] = $website->user_id;
        $data['gateway'] = 'stripe';
        $data['status'] = 'PENDING';
        $data['checkin'] = \Carbon\Carbon::createFromFormat('Y-m-d', $data['checkin'], $listing->timezone_name);
        $data['checkout'] = \Carbon\Carbon::createFromFormat('Y-m-d', $data['checkout'], $listing->timezone_name);
        $booking = \App\Models\Booking::create($data); 

        //Store booking in iCal calendar

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

        if(!$website->email)
        {
            return response()->json(['message' => 'Host has not provided a sending email'], 401);
        }

        if($listing->minimum_nights && $data['end'])

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
            'guests' => 'integer|required|min:1'
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

        //Set guest factor
        if($listing->pricing_type == 'per_listing'){
            $guests = 1;
        }
        else{
            $guests = $data['guests'];
        }

        //Set price
        $price = 0;
        if($data['nights']<7)
        {
            $price = round($guests*$data['nights']*$listing->price);
        }
        elseif($data['nights']>=7 && $data['nights']<28)
        {
            $price = round($guests*$data['nights']*$listing->price*$listing->weekly_factor);
        }
        elseif($data['nights']>=28)
        {
            $price = round($guests*$data['nights']*$listing->price*$listing->monthly_factor);
        }

        //Stripe Zero-decimal currency case
        $zero_decimal_currency = ["BIF","CLP","DJF","GNF","JPY","KMF","KRW","MGA","PYG","RWF","UGX","VND","VUV","XAF","XOF","XPF"];

        $payment_intent = \Stripe\PaymentIntent::create([
          'payment_method_types' => ['card'],
          'amount' => !in_array($listing->currency, $zero_decimal_currency) ?  $price*100 : $price,
          'currency' => $listing->currency,
          'application_fee_amount' => 0,
        ], ['stripe_account' => $website->stripe_account->account_id]);

        return response()->json(['message' => 'Payment intent successfully generated', 'client_secret' => $payment_intent->client_secret], 200);
    }
}
