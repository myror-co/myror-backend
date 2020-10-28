<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Resources\Listing as ListingResource;
use App\Http\Resources\Website as WebsiteResource;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($website_id)
    {
        $website = \App\Models\Website::firstWhere('api_id', $website_id);

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 200);
        }

        $listings = $website->listings;

        return ListingResource::collection($listings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($website_id, Request $request)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 400);
        }

        $data = $request->validate([
            'url' => 'required|string'
        ]);

        //Get airbnb id from URL
        $slice = Str::of($data['url'])->after('rooms/');
        $airbnb_id = Str::of($slice)->explode('?')[0];

        $listing = \App\Models\Listing::where('airbnb_id', $airbnb_id)->first();

        if ($listing) 
        {
            return response()->json(['message' => 'This listing has been already imported to Myror'], 400);
        }

        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.airbnb.com/v1/listings/'.$airbnb_id.'?client_id='.env('AIRBNB_CLIENT_ID');

        $response = $client->request('GET', $endpoint);

        if ($response->getStatusCode() != 200)
        {
            return response()->json(['message' => 'Error while communicating with Airbnb'], 400);
        }
      
        //Create listing
        $listing_data = json_decode($response->getBody()->getContents(), true);

        $listing = \App\Models\Listing::create([
            'website_id' => $website->id, 
            'user_id' => Auth::id(), 
            'airbnb_id' => $airbnb_id, 
            'name' => $listing_data['listing']['name'] ?? null, 
            'picture_sm' => $listing_data['listing']['medium_url'] ?? null, 
            'picture_xl' => $listing_data['listing']['xl_picture_url'] ?? null, 
            'price' => $listing_data['listing']['price'] ?? null, 
            'currency' => $listing_data['listing']['native_currency'] ?? null, 
            'city'=> $listing_data['listing']['city'] ?? null, 
            'country'=> $listing_data['listing']['country'] ?? null, 
            'smart_location'=> $listing_data['listing']['smart_location'] ?? null, 
            'bathrooms'=> $listing_data['listing']['bathrooms'] ?? null, 
            'bedrooms'=> $listing_data['listing']['bedrooms'] ?? null, 
            'beds'=> $listing_data['listing']['beds'] ?? null, 
            'capacity'=> $listing_data['listing']['person_capacity'] ?? null, 
            'property_type'=> $listing_data['listing']['property_type'] ?? null, 
            'room_type'=> $listing_data['listing']['room_type'] ?? null, 
            'summary'=> $listing_data['listing']['summary'] ?? null, 
            'description'=> $listing_data['listing']['description'] ?? null, 
            'space'=> $listing_data['listing']['space'] ?? null, 
            'neighborhood'=> $listing_data['listing']['neighborhood_overview'] ?? null, 
            'amenities'=> $listing_data['listing']['amenities'] ?? null, 
            'checkout_time'=> $listing_data['listing']['check_out_time'] ?? null, 
            'photos'=> $listing_data['listing']['photos'] ?? null, 
            'recent_review'=> $listing_data['listing']['recent_review']['review'] ?? null, 
        ]); 

        return response()->json(['message' => 'Room successfully added', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($website_id, $listing_id, Request $request)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 400);
        }

        if ($website->listings->count() <= 1) 
        {
            return response()->json(['message' => 'You cannot have less than a room per website'], 401);
        }

        $listing = \App\Models\Listing::where('user_id', Auth::id())->where('id', $listing_id)->first();

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 400);
        }

        //Delete website from database
        if ($listing->delete())
        {
            return response()->json(['message' => 'Listing successfully deleted', 'website' => new WebsiteResource($website)], 200);
        }
        else
        {
            return response()->json(['message' => 'Listing cannot be deleted'], 400);
        }
    }
}
