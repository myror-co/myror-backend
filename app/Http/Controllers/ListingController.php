<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use ICal\ICal;
use Carbon\Carbon;
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
            return response()->json(['message' => 'Website not found'], 404);
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
            'name' => $listing_data['listing']['name'] ? preg_replace("/[^a-zA-Z0-9\s]/", "", $listing_data['listing']['name']) : null, 
            'slug' => $listing_data['listing']['name'] ? Str::slug(preg_replace("/[^a-zA-Z0-9\s]/", "", $listing_data['listing']['name']), '-') : null, 
            'picture_sm' => $listing_data['listing']['medium_url'] ?? null, 
            'picture_xl' => $listing_data['listing']['xl_picture_url'] ?? null, 
            'price' => $listing_data['listing']['price'] ?? null, 
            'currency' => $listing_data['listing']['native_currency'] ?? null, 
            'city'=> $listing_data['listing']['city'] ?? null, 
            'country'=> $listing_data['listing']['country'] ?? null, 
            'smart_location'=> $listing_data['listing']['smart_location'] ?? null, 
            'lat'=> $listing_data['listing']['lat'] ?? null, 
            'lng'=> $listing_data['listing']['lng'] ?? null, 
            'user'=> $listing_data['listing']['user']['user'] ?? null,
            'hosts'=> $listing_data['listing']['hosts'] ?? null,  
            'bathrooms'=> $listing_data['listing']['bathrooms'] ?? null, 
            'bedrooms'=> $listing_data['listing']['bedrooms'] ?? null, 
            'beds'=> $listing_data['listing']['beds'] ?? null, 
            'capacity'=> $listing_data['listing']['person_capacity'] ?? null, 
            'property_type'=> $listing_data['listing']['property_type'] ?? null, 
            'room_type'=> $listing_data['listing']['room_type'] ?? null, 
            'summary'=> $listing_data['listing']['summary'] ?? null, 
            'description'=> $listing_data['listing']['description'] ? Str::of($listing_data['listing']['description'])->limit(1395) : null,  
            'space'=> $listing_data['listing']['space'] ?? null, 
            'neighborhood'=> $listing_data['listing']['neighborhood_overview'] ?? null, 
            'amenities'=> $listing_data['listing']['amenities'] ?? null, 
            'checkout_time'=> $listing_data['listing']['check_out_time'] ?? null, 
            'photos'=> $listing_data['listing']['photos'] ?? null, 
            'recent_review'=> $listing_data['listing']['recent_review']['review'] ?? null,
            'reviews_count'=> $listing_data['listing']['reviews_count'] ?? null, 
            'rating'=> $listing_data['listing']['star_rating'] ?? null,
            'rules'=> $listing_data['listing']['guest_controls'] ?? null,  
        ]); 

        return response()->json(['message' => 'Room successfully added', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($website_name, $listing_id)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('name', $website_name)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $listing = \App\Models\Listing::find($listing_id);

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        return new ListingResource($listing);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCalendar($website_id, $listing_id)
    {
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

        $calendar = $listing->calendar_link;

        if(!$calendar)
        {
            return response()->json(['not_available' => []], 200);
        }

        try {
            $ical = new ICal('ICal.ics', array(
                'defaultSpan'                 => 2,     // Default value
                'defaultTimeZone'             => 'UTC',
                'defaultWeekStart'            => 'MO',  // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter'             => null,  // Default value
                'filterDaysBefore'            => null,  // Default value
                'skipRecurrence'              => false, // Default value
            ));
            // $ical->initFile('ICal.ics');
            $ical->initUrl($calendar, $username = null, $password = null, $userAgent = null);

            $not_available_dates = [];

            foreach ($ical->events() as $key => $value) {
                $start_date = Carbon::parse($value->dtstart_tz);
                $end_date = Carbon::parse($value->dtend_tz);

                for($date = $start_date; $date->lt($end_date); $date->addDay()) {
                    $not_available_dates[] = $date->format('Y-m-d');
                }
            }

            return response()->json(['not_available' => $not_available_dates], 200);
        } catch (\Exception $e) {
            die($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkAvailable(Request $request, $website_id, $listing_id)
    {
        $data = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
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

        $calendar = $listing->calendar_link;

        if(!$calendar)
        {
            return response()->json(['message' => 'There are no available calendar'], 404);
        }

        try {
            $ical = new ICal('ICal.ics', array(
                'defaultSpan'                 => 2,     // Default value
                'defaultTimeZone'             => 'UTC',
                'defaultWeekStart'            => 'MO',  // Default value
                'disableCharacterReplacement' => false, // Default value
                'filterDaysAfter'             => null,  // Default value
                'filterDaysBefore'            => null,  // Default value
                'skipRecurrence'              => false, // Default value
            ));
            // $ical->initFile('ICal.ics');
            $ical->initUrl($calendar, $username = null, $password = null, $userAgent = null);

            $is_available = count($ical->eventsFromRange($data['start'], $data['end'])) > 0 ? false : true;

            return response()->json(['is_available' => $is_available, 'start_date' => $data['start'], 'end_date' => $data['end']], 200);
        } catch (\Exception $e) {
            die($e);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $website_id, $listing_id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:40',
            'description' => 'string|max:1400',
            'neighborhood' => 'string|max:1000',
            'calendar_link' => 'url|nullable|max:500',
        ]);

        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $website_id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 400);
        }

        $listing = \App\Models\Listing::find($listing_id);

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        //Update only existig fields
        $listing->fill($data);
        $listing->save();

        return response()->json(['message' => 'Room information updated successfully'], 200);
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
