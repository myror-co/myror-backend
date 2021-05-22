<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('oauth/register', 'AuthController@register');
Route::post('oauth/login', 'AuthController@login');
Route::get('oauth/login/{provider}', 'AuthController@redirectToProvider');
Route::get('oauth/login/{provider}/callback', 'AuthController@handleProviderCallback');

//reset password
Route::post('password/request', 'AuthController@requestReset')->name('password.request');
Route::post('password/reset', 'AuthController@resetPassword')->name('password.reset');

//Verify email
Route::get('email/verify/{id}', 'AuthController@verify')->name('verification.verify'); // Make sure to keep this as your route name
Route::get('email/resend', 'AuthController@resend')->name('verification.resend');

// Route::post('upload/files', 'WebsiteController@upload');
Route::get('site/{id}', 'WebsiteController@publicData');
Route::get('site/{id}/instagram', 'WebsiteController@getInstagramPosts');
Route::get('site/{website_id}/rooms/{listing_id}/calendar', 'ListingController@getCalendar');
Route::get('site/{website_id}/rooms/{listing_id}/calendar/available', 'ListingController@checkAvailable');
Route::post('site/{website_id}/rooms/{listing_id}/requestBooking', 'BookingController@requestBooking');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

	Route::get('user/me', function(){
		return response()->json(['user' => Auth::user()], 200);
	});

	Route::put('websites/{id}/analytics', 'WebsiteController@addAnalytics');
	Route::delete('websites/{id}/analytics', 'WebsiteController@deleteAnalytics');
	Route::put('websites/{id}/domain', 'WebsiteController@addDomain');
	Route::delete('websites/{id}/domain', 'WebsiteController@deleteDomain');
	Route::delete('websites/{id}/logo', 'WebsiteController@deleteLogo');

	//Subscriptions
	Route::get('subscription/intent', 'SubscriptionController@getIntent');
	Route::post('subscription/upgrade', 'SubscriptionController@upgrade');


	Route::apiResources([
	    'addons' => AddonController::class,
	    'instagrams' => InstagramPluginController::class,
	    'menus.items' => MenuItemController::class,
		'user' => UserController::class,
	    'websites' => WebsiteController::class,
	    'websites.listings' => ListingController::class,
	]);

	Route::post('oauth/logout', 'AuthController@logout');
});