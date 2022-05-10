# # Archetype-dev PHP-SDK 

This guide is showing installtion and configuration and some examples on how to use Archetype SDK on your Laravel application.

If your PHP application is not Laravel based, [follow the insturctions here](https://github.com/ArchetypeAPI/php-sdk)
<br/>

# Installation & configuration

## Install Archetype SDK through composer
```bash 
 composer require archetype-dev/php-sdk 
```
<br/>

After you installed the SDK, you need to publish the config file; to do so, run this command: 
```bash
php artisan vendor:publish --provider="Archetype\ArchetypeServiceProvider"
```
<br/>

Then, open the .env file and add the app id and secret key for your archetype app, just like shown below:

```env
ARCHETYPE_APP_ID="86c7324044ed499999999999999"
ARCHETYPE_SECRET_KEY="archetype_sk_prod_927ea9e96cc74543888888888888888"
```
<br/>

Then, run this command to clear the config cache so the configuration takes effect
```bash
php artisan config:cache
```

That's it for the installation and configuration.
<br/>

## Using Archetype Authentication
You can use archetype authentication to authorize your users for certain routes, just like you would in your normal Laravel application with `auth` middleware.


 
 Before you start using Archetype auth system, you need 3 steps:
 1. Register your user and store their api key. 
 2. Then Subscribe that user to a specific product/plan using Archetype::createCheckoutSession method as explained in the bottom.
 3. When user requesting authorized routes, the url must include `apikey`, either as a header or input or url query, for example ``your-domain.com/user-dashboard?apikey=user-api-key-you-got-from-registering-archetype-user``

 <br/>
 Now you can protect routes with our authentication by adding the `auth.archetype` middleware in routes you want to be authorized, just like the example shown below:
 
 ```php
 <?php 
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Route;
  
  Route::get('/home', function () {
    echo 'This route is protected by archetype auth system.';
  })->middleware('auth.archetype');
  
 ```
 
 ## Register your users
 Archetype provides a way to register new users and new API Keys via our SDK, see the below example: 
  ```php
 <?php 
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Route;
  use Archetype\Archetype;
  
  Route::get('/register-user', function () {
    $user = Archetype::registerUser('CUSTOM-UID', 'Archetype Team', 'hello@archetype.dev');
    return response($user);
  });
  
 ```
 You can pass a custom uid which is a unique identifier for each user and optionally add details like their emails to register a new user. 
 You'll soon be able to add custom attributes and flags for each user. This will automatically create an API key.
 
 Below is a sample response after generating a new user

```json
{
 "apikeys": [
  "0d15b36c917a43f282d1a6e3b"
 ],
 "app_id": "699dd288309f4c9992cb9437eeeeeee",
 "attrs": [],
 "custom_uid": "abei9394aefff303e22eee",
 "deleted_at": null,
 "description": null,
 "email": "hello@archetype.dev",
 "first_seen": 1651591806.863881,
 "group": null,
 "is_new": true,
 "is_trial": false,
 "last_seen": 1651591806.863886,
 "live_mode": false,
 "name": "Archetype Team",
 "quota": 0,
 "renewal_number": 0,
 "sandbox_end": null,
 "status": "not_subscribed",
 "stripe_uid": "cus_LccvjqG11c",
 "subscription_date": null,
 "subscription_id": null,
 "tier_id": null,
 "trial_end": null,
 "uid": "626e7734ff19437783cb2919bceeeeeeeb"
}
```
The API Key will not be tied to a specific plan unless the user subscribes.

## Retrieve User

When the user is logged in and authorized whether via a session based token or however you think about auth, you can actually pass their custom_uid unique identifier to get their details like API keys, quota, usage and more. 
More details can be found in the Users page.

```php
 <?php 
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Route;
  use Archetype\Archetype;
  
  Route::get('/user', function () {
    $user = Archetype::getUser('CUSTOM-UID');
    return response($user);
  });
  
 ```
 This returns a user JSON object

```json
{
 "apikeys": [
 "0d15b36c917a43f282d1a6e3bbeeeeeee"
 ],
 "app_id": "699dd288309f4c9992cb9437e39eeeee",
 "attrs": [],
 "custom_uid": "abei9394aefff303eeeeee",
 "email": "hello@archetype.dev",
 "first_seen": 1651591806.863881,
 "has_quota": false,
 "is_new": true,
 "is_trial": false,
 "last_seen": 1651591806.863886,
 "last_updated": 1651590222.462463,
 "live_mode": false,
 "renewal_number": 0,
 "start_time": 1651590222.462468,
 "status": "active",
 "stripe_app_id": "acct_1KtwzhGhb9fn7iOb",
 "stripe_subscription_id": "sub_1KvNggGhb9fe",
 "subscription_date": 1651748509.594179,
 "subscription_id": "sub_1KvNggGhb9fn7iObpeee",
 "tier_id": null,
 "uid": "626e7734ff19437783cb2919beeeeeeeee"
}
```

## Retrieve Available Products
This function returns all the products that you currently and publicly offer to your users. Pulling this list is how you can dynamically render prices.

```php
 <?php 
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Route;
  use Archetype\Archetype;
  
  Route::get('/products', function () {
    $products = Archetype::getProducts();
    return response($products);
  });
  
 ```
This returns a JSON object that is a list of products.

```javascript
[
  {
    app_id: 'YOUR_APP_ID',
    currency: 'usd',
    description: 'Basic tier',
    endpoints: [],
    has_full_access: true,
    has_quota: true,
    has_trial: true,
    is_active: true,
    is_free: false,
    is_new: true,
    name: 'Basic',
    period: 'month',
    price: 124,
    quota: 1000,
    tier_id: 'YOUR_TIER_ID',
    trial_length: 7,
    trial_time_frame: 'days'
  }
]
```

## Generate Checkout Sessions.

Once you get a product, you can pass the tier_id provided in the getproducts function and the user's custom_uid to generate a checkout session url.

What this does is create an ephemeral link to stripe that allows the user to enter their credit card details to purchase a subscription. This handles both creating and updating a checkout session.

The function returns a URL which you can then use to redirect a user to. In your API Settings Page on the Archetype side, you can set a return and redirect url after they've completed (or cancelled) the checkout process.

```php
 <?php 
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Route;
  use Archetype\Archetype;
  
  Route::get('/create-checkout-session', function () {
    $checkoutUrl = Archetype::createCheckoutSession('CUSTOM-UID', 'tier_id');
    return response()->json($checkoutUrl);
  });
  
 ```
 
 ## Cancel Products

We lastly provide an easy functionality for you to allow a user to cancel their subscription without any headache on your end.

```php
 <?php 
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Route;
  use Archetype\Archetype;
  
  Route::get('/cancel-subscription', function (Request $request) {
    $res = Archetype::cancelSubscription('CUSTOM_UID');
    return response()->json($res);
  });
  ```
  
## Tracking without Middleware

If you want to track individual endpoints, you can simply add a track function to the call that'll asynchronously log the call without any further input.

You can optionally supply the user's API Key or their Custom uid that you provided to track events based on users.

```php 
<?php 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Archetype\Archetype;

Route::get('/', function(Request $request) {
    Archetype::log('user-apikey', $request);
    return response('Hello World!!')
});
```
<br/>

That's it. To learn more, head over to [Archetype.dev](https://docs.archetype.dev/docs).
