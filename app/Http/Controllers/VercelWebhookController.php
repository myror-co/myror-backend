<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\UpdateBuildStatusVercel;

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

        UpdateBuildStatusVercel::dispatch($payload);

        return response()->json(['message' => 'Vercel webhook dispatched'], 200);
    }
}
