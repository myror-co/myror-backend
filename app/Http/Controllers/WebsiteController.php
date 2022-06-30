<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Website as WebsiteResource;
use App\Http\Resources\WebsitePublic as WebsitePublicResource;
use Illuminate\Support\Facades\Bus;
use App\Jobs\CreateNewVercelProject;
use App\Jobs\DeployNewSiteVercel;
use App\Jobs\DeleteVercelProject;
use App\Jobs\AddCustomDomain;
use App\Jobs\AddNewEnvironmentVariable;
use App\Jobs\DeleteEnvironmentVariable;
use App\Jobs\RedeploySiteVercel;
use App\Jobs\DeleteCustomDomain;
use App\Jobs\RedirectDomain;
use GuzzleHttp\Exception\RequestException;


class WebsiteController extends Controller
{
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
        //Check if user can create new website
        if(Auth::user()->websites->count() > 0 && !Auth::user()->subscribed('default'))
        {
            return response()->json(['message' => 'You can only create up to 1 website with the Starter plan'], 401);
        }

        $data = $request->validate([
            'name' => 'required|alpha_dash|unique:websites|max:40',
            'url' => 'required|url'
        ]);

        //Parse airbnb listing ID 
        if(!Str::of($data['url'])->containsAll(['airbnb', 'rooms']) && !Str::of($data['url'])->containsAll(['airbnb', 'luxury', 'listing']))
        {
            return response()->json(['message' => 'We cannot find an Airbnb listing from the given URL'], 404);
        }

        //Get airbnb id from URL
        if(Str::of($data['url'])->containsAll(['airbnb', 'rooms']))
        {
            $slice = Str::of($data['url'])->after('rooms/');
            $airbnb_id = Str::of($slice)->explode('?')[0];
        }
        else{//luxury case
            $slice = Str::of($data['url'])->after('listing/');
            $airbnb_id = Str::of($slice)->explode('?')[0];
        } 

        $listing = \App\Models\Listing::where('airbnb_id', $airbnb_id)->first();

        if ($listing) 
        {
            return response()->json(['message' => 'This listing has been already imported to Myror'], 400);
        }

        //Fetch Listing from Airbnb API 
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.airbnb.com/v1/listings/'.$airbnb_id.'?client_id='.env('AIRBNB_CLIENT_ID');

        try {
            $response = $client->request('GET', $endpoint);
        }
        catch (RequestException $e) {
            if($e->getCode() == 404)
            {
                return response()->json(['message' => 'Make sure your listing is published on Airbnb and retry'], 404);
            }

            return response()->json(['message' => 'Error while communicating with Airbnb'], 400);
        }

        //Fetch Reviews from Airbnb API 
        $endpoint = 'https://api.airbnb.com/v2/homes_pdp_reviews?listing_id='.$airbnb_id.'&limit=8&offset=0&client_id='.env('AIRBNB_CLIENT_ID');

        $review_response = $client->request('GET', $endpoint);

        if ($review_response->getStatusCode() == 200)
        {
            $reviews_data = json_decode($review_response->getBody()->getContents(), true);
        }

        //Create website
        $website_data['user_id'] = Auth::id();
        $website_data['api_id'] = Str::uuid();
        $website_data['name'] = Str::lower($data['name']); //vercel only accepts lowercap
        $website_data['status'] = 'initiated';

        $website = \App\Models\Website::create($website_data);            

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
            'recent_review'=> $reviews_data['reviews'] ?? null,
            'reviews_count'=> $listing_data['listing']['reviews_count'] ?? null, 
            'rating'=> $listing_data['listing']['star_rating'] ?? null,
            'rules'=> $listing_data['listing']['guest_controls'] ?? null,  
        ]); 

        //Update website data
        $supported_currencies = ["USD","AUD","BRL","CAD","CHF","CZK","DKK","EUR","GBP","HKD","HUF","ILS","JPY","MYR","MXN","NOK","NZD","PHP","PLN","RUB","SEK","SGD","THB","TWD"];

        $website->update([
            'title' => $listing->name, 
            'main_picture' => $listing->picture_xl, 
            'description' => $listing->description,
            'currency' => Arr::exists($supported_currencies, $listing_data['listing']['native_currency']) ? $listing_data['listing']['native_currency'] : "USD"
        ]);

        //Launch job to Create new project on Vercel
        Bus::chain([
            new CreateNewVercelProject($website),
            new DeployNewSiteVercel($website),
        ])->dispatch();

        //Update subscription quantity
        if(Auth::user()->subscribed('default')){
            Auth::user()->subscription('default')->incrementQuantity();
        }
        
        //Update quantity o nSendinBlue
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.sendinblue.com/v3/contacts/'.Auth::user()->sendinblue_id;

        $response = $client->request('PUT', $endpoint,[
            'headers' => [
                'api-key' => env('SENDINBLUE_API_KEY')
            ],
            'json' => [
                'attributes' => ['SITES' => Auth::user()->websites()->count()],
                'listIds' => [2]
            ]
        ]);

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
            return response()->json(['message' => 'Website not found'], 404);
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
            return response()->json(['message' => 'Website not found'], 404);
        }

        return new WebsitePublicResource($website);
    }

    /**
     * Get latest instagram posts.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getInstagramPosts($id)
    {
        $website = \App\Models\Website::where('api_id', $id)->first();

        if (!$website)
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        if (!$website->instagram_plugin_id)
        {
            return response()->json(['message' => 'Instagram plugin not connected'], 404);
        }

        //Get media ids
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://graph.instagram.com/me/media?fields=id&access_token='.$website->instagram_plugin->access_token;

        $response = $client->request('GET', $endpoint);

        if ($response->getStatusCode() != 200)
        {
            return response()->json(['message' => 'We could not retrieved info from Instagram user'], 400);
        }

        $post_masters = json_decode($response->getBody()->getContents(), true)['data'];
        $insta_pic = [];

        foreach ($post_masters as $key => $post_master) {
            $endpoint = 'https://graph.instagram.com/'.$post_master['id'].'?fields=media_type,media_url&access_token='.$website->instagram_plugin->access_token;

            $response = $client->request('GET', $endpoint);

            if ($response->getStatusCode() != 200)
            {
                return response()->json(['message' => 'We could not retrieved media info from Instagram user'], 400);
            }      

            $picture_data = json_decode($response->getBody()->getContents(), true);
            if($picture_data['media_type'] == 'CAROUSEL_ALBUM')
            {
                $insta_pic[] = $picture_data['media_url'];
            }
            elseif($picture_data['media_type'] == 'IMAGE')
            {
                $insta_pic[] = $picture_data['media_url'];
            }
        }

        //Build array of instagram pictures

        return response()->json(['instagram_photos' => $insta_pic], 200);
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
            'title' => 'string',
            'icon' => 'image|mimes:jpg,png,jpeg|max:1024|dimensions:min_width=50,min_height=50,max_width=1000,max_height=1000',
            'main_picture' => 'image|mimes:jpg,png,jpeg|max:2048|dimensions:min_width=800,min_height=800,max_width=3000,max_height=2000',
            'description' => 'string',
            'meta_description' => 'string|max:160',
            'cancellation_policy' => 'string|max:2000',
            'no_show_policy' => 'string|max:2000',
            'deposit_policy' => 'string|max:2000',
            'other_policy' => 'string|max:2000',
            'facebook' => 'url|nullable',
            'instagram' => 'url|nullable',
            'google' => 'url|nullable',
            'phone' => 'string|nullable|max:20',
            'currency' => 'string|max:3',
            'whatsapp_number' => 'string|nullable|max:20',
            'email' => 'email|max:100|nullable',
            'calendar_link' => 'url|nullable|max:500',
            'instagram_plugin_id' => 'integer|nullable',
            'paypal_client_id' => 'string|nullable',
            'stripe_account_id' => 'integer|nullable'
        ]);

        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        //Get old values of stripe and paypal
        $old_website = $website->replicate();

        //Handle logo update
        if($request->hasFile('icon'))
        {
            $path = $request->file('icon')->storeAs(
                $website->api_id, 'logo-'.Str::uuid(), 's3'
            );
            $data['icon'] = $path;
        }

        //Handle logo update
        if($request->hasFile('main_picture'))
        {
            $path = $request->file('main_picture')->storeAs(
                $website->api_id, 'main_picture-'.Str::uuid(), 's3'
            );
            $data['main_picture'] = 'https://'.env('AWS_BUCKET').'.s3.amazonaws.com/'.$path;
        }

        //Update only existig fields
        $website->fill($data);
        $website->save();

        //Add env + redeploy
        if( (Arr::exists($data, 'paypal_client_id') && $data['paypal_client_id'] != $old_website->paypal_client_id) || (Arr::exists($data, 'stripe_account_id') && $data['stripe_account_id'] != $old_website->stripe_account_id) || (Arr::exists($data, 'currency') && $data['currency'] != $old_website->currency && $website->paypal_client_id))
        {
            if($website->stripe_account_id != $old_website->stripe_account_id)
            {
                if(!$website->stripe_account_id && $old_website->stripe_account_id){
                    DeleteEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_STRIPE_ACCOUNT_ID');
                }
                elseif($website->stripe_account_id && $old_website->stripe_account_id)
                {
                    Bus::chain([
                        new DeleteEnvironmentVariable($website, 'NEXT_PUBLIC_STRIPE_ACCOUNT_ID'),
                        new AddNewEnvironmentVariable($website, 'NEXT_PUBLIC_STRIPE_ACCOUNT_ID', $website->stripe_account->account_id)
                    ])->dispatch();
                }
                else{
                    AddNewEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_STRIPE_ACCOUNT_ID', $website->stripe_account->account_id);
                }
            }

            if($website->paypal_client_id != $old_website->paypal_client_id)
            {
                if(!$website->paypal_client_id){
                    DeleteEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_PAYPAL_CLIENT_ID');
                    DeleteEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_PAYPAL_CURRENCY');
                }
                else{
                    AddNewEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_PAYPAL_CLIENT_ID', $website->paypal_client_id);
                    AddNewEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_PAYPAL_CURRENCY', $website->currency);
                }
            }

            if($website->currency != $old_website->currency)
            {
                DeleteEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_PAYPAL_CURRENCY');
                AddNewEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_PAYPAL_CURRENCY', $website->currency);
            }

            RedeploySiteVercel::dispatch($website);
        }

        return response()->json(['message' => 'Site settings updated successfully', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addDomain(Request $request, $id)
    {
        //Check if user can add listing
        if(!Auth::user()->subscribed('default'))
        {
            return response()->json(['message' => 'You cannot add Custom domains with the Starter plan'], 401);
        }

        $data = $request->validate([
            'custom_domain' => 'required|unique:websites|regex:/^(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{1,63}(?<!-)\.)+[a-zA-Z]{2,63}$)/i'
        ]);

        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        //Update only existig fields
        $website->fill($data);
        $website->save();

        //Add domain to vercel
        Bus::chain([
            new AddCustomDomain($website),
            new DeleteEnvironmentVariable($website, 'NEXT_PUBLIC_SITE_URL'),
            new AddNewEnvironmentVariable($website, 'NEXT_PUBLIC_SITE_URL', $website->custom_domain),
            new RedeploySiteVercel($website)
        ])->dispatch();

        return response()->json(['message' => 'Domain successfully updated', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addAnalytics(Request $request, $id)
    {
        $data = $request->validate([
            'google_gtag_id' => 'alpha_dash|max:20|nullable'
        ]);

        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        if($website->google_gtag_id != $data['google_gtag_id'])
        {
            if(!$data['google_gtag_id'] && $website->google_gtag_id){
                DeleteEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_GOOGLE_ANALYTICS_ID');
            }
            elseif($data['google_gtag_id'] && $website->google_gtag_id)
            {
                Bus::chain([
                    new DeleteEnvironmentVariable($website, 'NEXT_PUBLIC_GOOGLE_ANALYTICS_ID'),
                    new AddNewEnvironmentVariable($website, 'NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', $data['google_gtag_id'])
                ])->dispatch();
            }
            else{
                AddNewEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', $data['google_gtag_id']);
            }

            RedeploySiteVercel::dispatch($website);
        }

        //Update only existing fields
        $website->fill($data);
        $website->save();

        return response()->json(['message' => 'Google Analytics successfully updated', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addFacebookPixel(Request $request, $id)
    {
        $data = $request->validate([
            'facebook_pixel_id' => 'alpha_dash|max:20|nullable'
        ]);

        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        if($website->facebook_pixel_id != $data['facebook_pixel_id'])
        {
            if(!$data['facebook_pixel_id'] && $website->facebook_pixel_id){
                DeleteEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_FACEBOOK_PIXEL_ID');
            }
            elseif($data['facebook_pixel_id'] && $website->facebook_pixel_id)
            {
                Bus::chain([
                    new DeleteEnvironmentVariable($website, 'NEXT_PUBLIC_FACEBOOK_PIXEL_ID'),
                    new AddNewEnvironmentVariable($website, 'NEXT_PUBLIC_FACEBOOK_PIXEL_ID', $data['facebook_pixel_id'])
                ])->dispatch();
            }
            else{
                AddNewEnvironmentVariable::dispatch($website, 'NEXT_PUBLIC_FACEBOOK_PIXEL_ID', $data['facebook_pixel_id']);
            }

            RedeploySiteVercel::dispatch($website);
        }

        //Update only existing fields
        $website->fill($data);
        $website->save();

        return response()->json(['message' => 'Facebook Pixel successfully updated', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Update template.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTemplate($id)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website)
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        //Block if update too recent
        if($website->last_built_at > now()->subHour())
        {
            return response()->json(['message' => 'Your site was already updated recently.'], 401);
        }

        //Redeploy template
        $website->last_update_request_at = now();
        $website->save();

        RedeploySiteVercel::dispatch($website);

        return response()->json(['message' => 'Update successfully started', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteDomain(Request $request, $id)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        //Update only existing fields
        $domain_name = $website->custom_domain;
        $website->custom_domain = NULL;
        $website->save();

        //Delete custom domain
        Bus::chain([
            new DeleteCustomDomain($website, $domain_name),
            new DeleteEnvironmentVariable($website, 'NEXT_PUBLIC_SITE_URL'),
            new AddNewEnvironmentVariable($website, 'NEXT_PUBLIC_SITE_URL', $website->name.env('DEFAULT_MYROR_DOMAIN')),
            new RedeploySiteVercel($website)
        ])->dispatch();

        return response()->json(['message' => 'Domain successfully deleted', 'website' => new WebsiteResource($website)], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteLogo(Request $request, $id)
    {
        $website = \App\Models\Website::where('user_id', Auth::id())->where('api_id', $id)->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        //Delete icon from S3 storage
        Storage::disk('s3')->delete($website->icon);

        //Update only existing fields
        $website->icon = NULL;
        $website->save();

        return response()->json(['message' => 'Logo successfully deleted', 'website' => new WebsiteResource($website)], 200);
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
            //Update subscription quantity
            if(Auth::user()->subscribed('default')){
                Auth::user()->subscription('default')->decrementQuantity();
            }

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
