<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\UpdateBuildStatusVercel;
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
        $signature = $request->hasHeader('x-vercel-signature') ? $request->header('x-vercel-signature') : '';

        $hash_hmac = hash_hmac('sha1', $request->getContent(), env('VERCEL_WEBHOOK_SECRET'));

        //Check signature
        if($signature == $hash_hmac)
        {
            UpdateBuildStatusVercel::dispatch($payload);
        }
        

        return response()->json(['message' => 'Vercel webhook dispatched'], 200);
    }
}
