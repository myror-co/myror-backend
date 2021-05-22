<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Resources\User as UserResource;

use Laravel\Cashier\Exceptions\IncompletePayment;

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
        $request->user()->newSubscription(
            'default', 'price_1IpoGKLoqoklr6qpKb2uYOGY'
        )->create($request->paymentMethod,[
            'email' => $request->user()->email,
            'name' => $request->cardName
        ]);

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

    public function cancel(Request $request)
    {
        $user = $request->user();

        if($sub = $user->activeSubscription())
        {
            try{
                $user->subscription($sub->name)->cancel();
            }
            catch(Exception $e)
            {
                Bugsnag::notifyException($e);
                return response([
                    'message' => 'Error while trying to cancel',
                ], 400);
            }

            return response([
                'message' => 'Your plan has been canceled',
                'user' => new UserResource($user)
            ], 200);
        }

        return $response = response([
                'title' => 'error',
                'message' => 'No subscription found'
            ], 400);
    }

    public function resume(Request $request)
    {
        $user = $request->user();

        if($sub = $user->activeSubscription())
        {
            try{
                $user->subscription($sub->name)->resume();
            }
            catch(Exception $e)
            {
                Bugsnag::notifyException($e);
                return response([
                    'message' => 'Error while trying to resume',
                ], 400);
            }

            return response([
                'message' => 'Your plan has successfully resumed',
                'user' => new UserResource($user)
            ], 200);
        }

        return $response = response([
                'title' => 'error',
                'message' => 'No subscription found'
            ], 400);
    }

}
