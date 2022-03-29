<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\UpdateBuildStatusVercel;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
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
        if(!$request->getContent()){
            Bugsnag::notifyError('Vercel Web Hook', 'Invalid Body');
        }

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

    // public function setIntegration(Request $request)
    // {
    //     return redirect($request->input('next'));
    // }
}
