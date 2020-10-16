<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Website as WebsiteResource;
use Illuminate\Support\Facades\Bus;
use App\Jobs\CreateNewVercelProject;
use App\Jobs\DeployNewSiteVercel;
use App\Jobs\DeleteVercelProject;

class WebsiteController extends Controller
{
    public function upload()
    {

        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.vercel.com/v6/projects';

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Authorization' => 'Bearer '.env('VERCEL_TOKEN')
            ],
            'json' => ['name' => 'testerwer33']
        ]);

        return response()->json(json_decode($response->getBody()->getContents(), true));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $websites = Auth::user()->websites;

        return WebsiteResource::collection($websites);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|alpha_dash|unique:websites|max:50',
            'url' => 'required|string'
        ]);

        //Parse airbnb listing ID 
        if(!Str::of($data['url'])->containsAll(['airbnb', 'rooms']))
        {
            return response()->json(['message' => 'We cannot find an Airbnb listing from the given URL'], 400);
        }

        //Get airbnb id from URL
        $slice = Str::of($data['url'])->after('rooms/');
        $airbnb_id = Str::of($slice)->explode('?')[0];

        $listing = \App\Models\Listing::where('airbnb_id', $airbnb_id)->first();

        if ($listing) 
        {
            return response()->json(['message' => 'This listing has been already imported to Myror'], 400);
        }

        //Fetch Airbnb API
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.airbnb.com/v1/listings/'.$airbnb_id.'?client_id='.env('AIRBNB_CLIENT_ID');

        $response = $client->request('GET', $endpoint);

        if ($response->getStatusCode() != 200)
        {
            return response()->json(['message' => 'Error while communicating with Airbnb'], 400);
        }

        //Create website
        $website_data['user_id'] = Auth::id();
        $website_data['api_id'] = Str::uuid();
        $website_data['name'] = $data['name'];
        $website_data['status'] = 'initiated';

        $website = \App\Models\Website::create($website_data);            

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

        //Update website data
        $website->update([
            'title' => $listing->name, 
            'main_picture' => $listing->picture_xl, 
            'description' => $listing->description
        ]);

        //Launch job to Create new project on Vercel
        Bus::chain([
            new CreateNewVercelProject($website),
            new DeployNewSiteVercel($website),
        ])->dispatch();

        return response()->json(['message' => 'Website successfully created', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($name)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('name', $name)->first();

        if (!$website)
        {
            return response()->json(['message' => 'Website not found'], 400);
        }

        return new WebsiteResource($website);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function publicData($id)
    {
        $website = \App\Models\Website::where('api_id', $id)->first();

        if (!$website)
        {
            return response()->json(['message' => 'Website not found'], 400);
        }

        return new WebsiteResource($website);
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
        $data = $request->validate([
            'facebook' => 'string',
            'instagram' => 'string',
            'google' => 'string',
            'phone' => 'string|max:20',
            'email' => 'email',
        ]);

        $website = \App\Models\Website::where('api_id', $id)->first();
        // $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 400);
        }

        //Update only existig fields
        $website->fill($data);
        $website->save();

        return response()->json(['message' => 'Website updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();
        $website_name = $website->name;

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 200);
        }

        //Delete website from database
        if ($website->delete())
        {
            //Delete website from Vercel
            DeleteVercelProject::dispatch($website->name);

            return response()->json(['message' => 'Website successfully deleted'], 200);
        }
        else
        {
            return response()->json(['message' => 'Website cannot be deleted'], 200);
        }
    }
}
