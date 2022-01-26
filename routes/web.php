<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewBooking;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Stripe endpoint webhook
Route::post('/stripe/webhook','StripeWebhookController@handleWebhook');


//Stripe endpoint webhook
Route::post('/vercel/webhook','VercelWebhookController@handleWebhook');


Route::get('/test', function () {

        //Update quantity o nSendinBlue
        $client = new \GuzzleHttp\Client();
        $endpoint = 'https://api.sendinblue.com/v3/contacts/20';

        $response = $client->request('PUT', $endpoint,[
            'headers' => [
                'api-key' => env('SENDINBLUE_API_KEY')
            ],
            'json' => [
                'attributes' => ['SITES' => 2],
                'listIds' => [2]
            ]
        ]);
});