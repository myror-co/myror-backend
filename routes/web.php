<?php

use Illuminate\Support\Facades\Route;

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
