<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\InstagramPlugin as InstagramPluginResource;

class InstagramPluginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instagrams = Auth::user()->instagrams;

        return InstagramPluginResource::collection($instagrams);
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
            'code' => 'string|required'
        ]);

        //Exchange code for short lived token
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.instagram.com/oauth/access_token';

        $response = $client->request('POST', $endpoint,[
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'client_id' => env('INSTAGRAM_CLIENT_ID'),
                'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
                'code' => $data['code'],
            ]
        ]);

        if ($response->getStatusCode() != 200)
        {
            return response()->json(['message' => 'We could not retrieved the short lived token from Instagram'], 400);
        }

        $data_inter = json_decode($response->getBody()->getContents(), true);

        //Get long lived token
        $endpoint = 'https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret='.env('INSTAGRAM_CLIENT_SECRET').'&access_token='.$data_inter['access_token'];

        $response = $client->request('GET', $endpoint);

        if ($response->getStatusCode() != 200)
        {
            return response()->json(['message' => 'We could not retrieved the long lived token from Instagram'], 400);
        }

        $data = json_decode($response->getBody()->getContents(), true);

        //Get instagram user
        $endpoint = 'https://graph.instagram.com/me?fields=id,username&access_token='.$data['access_token'];

        $response = $client->request('GET', $endpoint);

        if ($response->getStatusCode() != 200)
        {
            return response()->json(['message' => 'We could not retrieved the userfrom Instagram'], 400);
        }

        $insta_user_data = json_decode($response->getBody()->getContents(), true);

        //Create website
        $insta_data['user_id'] = Auth::id();
        $insta_data['instagram_user_id'] = $data_inter['user_id'];
        $insta_data['instagram_username'] = $insta_user_data['username'];
        $insta_data['access_token'] = $data['access_token'];
        $insta_data['expires_in'] = $data['expires_in'];

        $instagram = \App\Models\InstagramPlugin::create($insta_data);   

        return response()->json(['message' => 'Instagram successfully linked'], 200);

    }
}
