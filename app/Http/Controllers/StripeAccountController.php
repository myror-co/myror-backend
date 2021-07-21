<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Resources\StripeAccount as StripeAccountResource;

class StripeAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->input('filter') == 'active'){
            $stripe_accounts = Auth::user()->stripe_accounts()->active()->get();
        }
        else{
            $stripe_accounts = Auth::user()->stripe_accounts;
        }

        return StripeAccountResource::collection($stripe_accounts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        //Prefill standard account
        $account = \Stripe\Account::create([
          'type' => 'standard',
          'email' => Auth::user()->email
        ]);

        //Create stripe account locally
        $stripe_account = \App\Models\StripeAccount::firstOrCreate([
            'account_id' => $account->id,
            'user_id' => Auth::user()->id
        ]); 

        //Create account link
        $account_links = \Stripe\AccountLink::create([
          'account' => $stripe_account->account_id,
          'refresh_url' => env('APP_URL').'/stripe/refresh/'.$stripe_account->id,
          'return_url' => env('APP_URL').'/stripe/return/'.$stripe_account->id,
          'type' => 'account_onboarding',
        ]);

        $stripe_account->setup_url = $account_links->url;
        $stripe_account->save();

        return response()->json(['message' => 'Stripe account successfully created', 'setup_url' => $stripe_account->setup_url], 200); 
    }

    public function refresh(Request $request, $id)
    {
        $stripe_account = \App\Models\StripeAccount::where('user_id', Auth::id())->where('id', $id)->first();

        if(!$stripe_account)
        {
            return response()->json(['message' => 'Stripe account not found'], 404);
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        //Create account link
        $account_links = \Stripe\AccountLink::create([
          'account' => $stripe_account->account_id,
          'refresh_url' => env('APP_URL').'/stripe/refresh/'.$id,
          'return_url' => env('APP_URL').'/stripe/return/'.$id,
          'type' => 'account_onboarding',
        ]);


        return response()->json(['message' => 'Stripe account successfully created', 'setup_url' => $account_links->url], 200);
    }

    public function return(Request $request, $id)
    {
        $stripe_account = \App\Models\StripeAccount::where('user_id', Auth::id())->where('id', $id)->first();

        if(!$stripe_account)
        {
            return response()->json(['message' => 'Stripe account not found'], 404);
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        //Retrieve account
        $account = \Stripe\Account::retrieve($stripe_account->account_id);

        $stripe_account->email = $account->email;
        $stripe_account->charges_enabled = $account->charges_enabled;
        $stripe_account->details_submitted = $account->details_submitted;
        $stripe_account->save();

        return response()->json(['message' => 'Stripe account successfully created', 'account' => $stripe_account, 'stripe' => $account], 200);
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
    public function destroy($id)
    {
        $stripe_account = \App\Models\StripeAccount::where('user_id', Auth::id())->where('id', $id)->first();

        if (!$stripe_account) 
        {
            return response()->json(['message' => 'Stripe account not found'], 400);
        }

        //Delete account from stripe
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

        //Create account link
        // $account_deleted = $stripe->accounts->delete($stripe_account->account_id);

        // if(!$account_deleted->deleted)
        // {
        //     return response()->json(['message' => 'Stripe account could not be deleted'], 400);
        // }

        //Update websites
        $websites = $stripe_account->websites;

        foreach ($websites as $website) {
           $website->stripe_account_id = null;
           $website->save();
        }

        $stripe_account->delete();

        return response()->json(['message' => 'Stripe account successfully deleted', 
                                'payments' => \App\Models\StripeAccount::where('user_id', Auth::id())->get()
                            ], 200);
    }
}
