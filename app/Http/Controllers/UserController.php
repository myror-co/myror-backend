<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;
use App\Http\Resources\User as UserResource;
use App\Jobs\DeleteVercelProject;
use App\Jobs\DeleteCustomDomain;
use App\Jobs\DeleteAccount;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user = Auth::user();

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => 'string|max:40',
        ]);

        $user = Auth::user();

        //Update only existig fields
        $user->fill($data);
        $user->save();

        return response()->json(['message' => 'User information updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        $user = Auth::user();

        //Get all websites from user
        $websites = $user->websites;

        //Delete dependencies on Vercel
        foreach ($websites as $key => $website) {
            Bus::chain([
                new DeleteCustomDomain($website, $website->custom_domain),
                new DeleteVercelProject($website->name)
            ])->dispatch();
        }

        DeleteAccount::dispatch($user);

        return response()->json(['message' => 'Your account has been successfully deleted'], 200);
    }
}
