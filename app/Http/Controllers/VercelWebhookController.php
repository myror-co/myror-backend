<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\WebsiteVercelBuilt;
use Illuminate\Support\Facades\Mail;
use Log;

class VercelWebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        Log::info($payload);

        $website = \App\Models\Website::where('vercel_project_id', $payload['data']['payload']['projectId'])->first();

        if (!$website) 
        {
            return response()->json(['message' => 'Website not found'], 404);
        }

        $user = $website->user;

        switch ($payload['data']['type']) 
        {
            case 'deployment':
                $website->status = 'deploying';
                $website->save();
            break;
            
            case 'deployment-ready':
                $website->status = 'built';
                $website->save();
    
                $site_url = $website->custom_domain ? 'https://'.$website->custom_domain : 'https://'.$website->name.'myror.website';

                //Send mail
                Mail::to($user->email)->queue(new WebsiteVercelBuilt($user->first_name, $website->name, $site_url));
            break;
        }

        return response()->json(['message' => 'Website updated'], 200);
    }
}
