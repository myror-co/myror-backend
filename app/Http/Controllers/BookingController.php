<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRequest;

class BookingController extends Controller
{
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

        $listing = \App\Models\Listing::find($listing_id);

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        //Sending mail

        Mail::to($website->email)
            ->queue(new BookingRequest($listing->name, $data['first_name'], $data['last_name'], $data['guests'], $data['start'], $data['end'], $data['message'], $data['phone'], $data['email']));

        return response()->json(['message' => 'Booking request successfully sent'], 200);
    }
}
