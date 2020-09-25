<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('password/email', 'AuthController@forgot')->name('password.forgot');
Route::post('password/reset', 'AuthController@reset')->name('password.reset');

Route::get('email/verify/{id}', 'AuthController@verify')->name('verification.verify'); // Make sure to keep this as your route name
Route::get('email/resend', 'AuthController@resend')->name('verification.resend');

Route::middleware(['auth:api', 'verified'])->group(function () {


	Route::post('oauth/logout', 'AuthController@logout');

});