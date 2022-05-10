<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use Archetype\Archetype;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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

Route::get('/register-user', function () {
    $user = Archetype::registerUser('CUSTOM-ID', 'Archetype Team', 'hello@archetype.dev');
    Log::info('user', [$user]);
    return response($user);
});
Route::get('/track', function (Request $request) {
    Archetype::log('apikey', $request);
    return response('Hello World!!');
});


Route::get('/products', function () {
    $products = Archetype::getProducts();
    return response($products);
});



Route::get('/user', function () {
    $user = Archetype::getUser('CUSTOM-ID');
    return response()->json($user);
});
  
Route::get('/create-checkout-session', function () {
    $checkoutUrl = Archetype::createCheckoutSession('CUSTOM-ID', 'PRODUCT-ID');
    return response()->json(['url' => $checkoutUrl]);
});


Route::get('/home', function () {
    echo 'This route is protected by archetype auth system.';
})->middleware('auth.archetype');

Route::get('/cancel-subscription', function (Request $request) {
    $res = Archetype::cancelSubscription('CUSTOM-ID');
    return response()->json($res);
});