<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Exceptions\IncompletePayment;
use App\Http\Resources\User as UserResource;

class SubscriptionController extends Controller
{
    public function getIntent(Request $request)
    {
        return response()->json(['intent' => Auth::user()->createSetupIntent()], 200);
    }

    public function show(Request $request)
    {
        $user = $request->user();

        if($sub = $user->hasSubscription())
        {
            $card = $user->defaultPaymentMethod()->asStripePaymentMethod()->card;
            $last_payment_id=null;

            if($user->hasIncompletePayment(config('services.stripe.plus.product_id')))
            {
                $last_payment_id= $user->subscription(config('services.stripe.plus.product_id'))->latestPayment()->id;
            }
            elseif($user->hasIncompletePayment(config('services.stripe.professional.product_id')))
            {
                $last_payment_id= $user->subscription(config('services.stripe.professional.product_id'))->latestPayment()->id;
            }
            elseif($user->hasIncompletePayment(config('services.stripe.enterprise.product_id')))
            {
                $last_payment_id= $user->subscription(config('services.stripe.enterprise.product_id'))->latestPayment()->id;
            }

            return $response = response([
                    'title' => 'success',
                    'subscription' => $user->subscription($sub->name),
                    'last_payment_id' => $last_payment_id,
                    'payment_method' => [
                                'card_brand' => $card->brand,
                                'exp_month' => $card->exp_month,
                                'exp_year' => $card->exp_year,
                                'last4' => $card->last4,
                            ],
                    'invoices' => $user->invoices()->map(function($invoice) {
                        return [
                            'id' => $invoice->id,
                            'date' => $invoice->date()->toFormattedDateString(),
                            'total' => $invoice->total/100,
                            'currency' => '$',
                            'pay_url' => $invoice->hosted_invoice_url,
                            'status' => $invoice->status,
                            'download' => '/download/user/invoice/' . $invoice->id,
                        ];}),
                    'intent' => $user->createSetupIntent()
                ], 200);
        }

        return $response = response([
                'title' => 'error',
                'message' => 'No subscription found'
            ], 400);
    }



    public function upgrade(Request $request)
    {
        if(!$request->user()->subscribed('default') && !$request->user()->hasIncompletePayment('default'))
        {
            try 
            {
                $request->user()->newSubscription(
                    'default', env('STRIPE_PRO_PRICE_ID')
                )->create($request->paymentMethod,[
                    'email' => $request->user()->email,
                    'name' => $request->user()->name
                ]);
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

	public function upgrade2(SubscriptionUpgradeRequest $request)
	{
		$user = $request->user();

        if(!$user->activeSubscription())
        {
            $company_name = $request->has('company_name') ? $request->input('company_name') : null;
            $company_address = $request->input('company_address');
            $company_zip = $request->input('company_zip');
            $company_city = $request->input('company_city');
            $company_country = $request->input('company_country');
            $cardholder_name = $request->input('cardholder_name');
            $plan = $request->input('plan');
            $billing_cycle = $request->input('billing_cycle');
            $paymentMethod = $request->input('payment_method');

            //Update company details
            $user->update([
            	'company_name' => $company_name,
            	'company_address' => $company_address,
            	'company_city' => $company_city,
            	'company_zip' => $company_zip,
            	'company_country' => $company_country,
            ]);

            //Subscribe to Stripe Plan
            switch ($plan) {
            	case "plus":
            		$product_id = config('services.stripe.plus.product_id');
            		$plan_id = $billing_cycle == 'monthly' ? config('services.stripe.plus.monthly') : config('services.stripe.plus.yearly') ;
                    break;

            	case "professional":
            		$product_id = config('services.stripe.professional.product_id');
            		$plan_id = $billing_cycle == 'monthly' ? config('services.stripe.professional.monthly') : config('services.stripe.professional.yearly') ;

            		break;

            	case "enterprise":
            		$product_id = config('services.stripe.enterprise.product_id');
            		$plan_id = $billing_cycle == 'monthly' ? config('services.stripe.enterprise.monthly') : config('services.stripe.enterprise.yearly') ;

            		break;

            	default:
            		# code...
            		break;
            }

            //Create subscription
            $plan_details = Plan::where('value', $plan)->first();

            try{
                $referral = $request->has('referral') ? $request->input('referral') : null;

                $user->newSubscription($product_id, $plan_id)
                     ->create($paymentMethod, [
                        'email' => $user->email,
                        'metadata' => array("referral" => $referral)
                    ]);                   

            }
            catch (IncompletePayment $exception) 
            {
                $user = $this->userRepository->updateMaxLeads($user->id, $plan_details->leads);
                $user = $this->userRepository->resetLeadUsed($user->id);

                //Add to mailing list
                $mailchimpAPI = new MailchimpAPI;
                $mailchimpAPI->addSubscriber($user->email, $user->first_name, $user->last_name, 'Paid', $user->created_at);

                return response([
                    'message' => 'We need one more step to verify your payment',
                    'payment_id' => $exception->payment->id,
                ], 403);
            }
            
            $user = $this->userRepository->updateMaxLeads($user->id, $plan_details->leads);
            $user = $this->userRepository->resetLeadUsed($user->id);

            //Add to mailing list
            $mailchimpAPI = new MailchimpAPI;
            $mailchimpAPI->addSubscriber($user->email, $user->first_name, $user->last_name, 'Paid', $user->created_at);

    		return response([
    			'user' => new UserResource($user)
    		], 200);
        }

        return response([
            'title' => 'error',
            'message' => 'Subscription already exists'
        ], 401);
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
