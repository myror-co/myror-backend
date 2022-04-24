<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Cashier\Exceptions\IncompletePayment;
use App\Http\Resources\User as UserResource;

class SubscriptionController extends Controller
{
    public function getIntent(Request $request)
    {
        return response()->json(['intent' => Auth::user()->createSetupIntent()], 200);
    }

    public function upgrade(Request $request)
    {
        $data = $request->validate([
            'promo_code' => 'nullable|string|max:50',
            'affiliate_id' => 'nullable|string|max:50'
        ]);

        if(!$request->user()->subscribed('default') && !$request->user()->hasIncompletePayment('default'))
        {
            try 
            {
                if($data['promo_code'])
                {
                    $valid_promo_code = \App\Models\Coupon::where('code', Str::upper($data['promo_code']))->first();

                    if($valid_promo_code)
                    {
                        $request->user()->newSubscription('default', env('STRIPE_PRO_PRICE_ID'))
                                        ->withPromotionCode($valid_promo_code->api_id)
                                        ->create($request->paymentMethod,[
                                            'email' => $request->user()->email,
                                            'name' => $request->user()->name
                                            ],
                                            [
                                                'metadata' => ['referral' => $data['affiliate_id'] ?? ""],
                                            ]
                                        );
                    }
                    else{
                        return response()->json(['message' => 'Promotion code cannot be applied'], 401);
                    }
                } else {
                    $request->user()->newSubscription('default', env('STRIPE_PRO_PRICE_ID'))
                                    ->create($request->paymentMethod,[
                                        'email' => $request->user()->email,
                                        'name' => $request->user()->name
                                        ],
                                        [
                                            'metadata' => ['referral' => $data['affiliate_id'] ?? ""],
                                        ]
                                    );                   
                }
            } 
            catch(IncompletePayment $exception)
            {
                return response()->json([
                    'user' => new UserResource(Auth::user()),
                    'message' => 'Payment verification needed', 
                    'verification_url' => env('API_URL').'/stripe/payment/'.$exception->payment->id.'?redirect='.env('APP_URL').'/billing'
                ], 403);
            }

            return response()->json(['message' => 'Subscribed successfully', 'user' => new UserResource(Auth::user())], 200); 
        }

        return response()->json(['message' => 'User already subscribed'], 403);
    }

    public function updateCustomer(Request $request)
    {
        $data = $request->validate([
            'address.line1' => 'required|string|max:255',
            'address.line2' => 'nullable|string|max:255',
            'address.city' => 'required|string|max:100',
            'address.country' => 'required|string|max:2',
            'address.state' => 'required|string|max:100',
            'address.postal_code' => 'required|string|max:50',
        ]);

        //Update Stripe customer
        Auth::user()->updateStripeCustomer($data);

        //Update local DB
        Auth::user()->fill([
            'address_line1' => $data['address']['line1'],
            'address_line2' => $data['address']['line2'],
            'address_city' => $data['address']['city'],
            'address_country' => $data['address']['country'],
            'address_state' => $data['address']['state'],
            'address_postal_code' => $data['address']['postal_code']
        ])->save();

        return response()->json(['message' => 'Billing details successfully updated'], 200);
    }

    public function updateCard(Request $request)
    {
        if (Auth::user()->hasPaymentMethod()) 
        {
            Auth::user()->updateDefaultPaymentMethod($request->paymentMethod);
            return response()->json(['message' => 'Payment method successfully updated', 'user' => new UserResource(Auth::user())], 200); 
        }

        return response()->json(['message' => 'User does not have any existing payment method'], 403);
    }

    public function deleteCard(Request $request, $id)
    {
        if (Auth::user()->hasPaymentMethod()) 
        {
            $paymentMethod = Auth::user()->findPaymentMethod($id);

            $paymentMethod->delete();

            return response()->json(['message' => 'Payment method successfully deleted', 'user' => new UserResource(Auth::user())], 200); 
        }

        return response()->json(['message' => 'User does not have any existing payment method'], 403);
    }

    public function cancel()
    {
        if(Auth::user()->subscribed('default'))
        {
            Auth::user()->subscription('default')->cancel();

            return response()->json(['message' => 'Subscription canceled successfully', 'user' => new UserResource(Auth::user())], 200); 
        }

        return response()->json(['message' => 'User is not subscribed'], 403);
    }

    public function resume()
    {
        if(Auth::user()->subscribed('default') && Auth::user()->subscription('default')->onGracePeriod())
        {
            Auth::user()->subscription('default')->resume();

            return response()->json(['message' => 'Subscription resumed successfully', 'user' => new UserResource(Auth::user())], 200); 
        }

        return response()->json(['message' => 'User is not subscribed or not on grace period'], 403);
    }
}
