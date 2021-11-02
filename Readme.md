<p align="center"><img src="https://s20.picofile.com/file/8442506218/unnamed_removebg_preview.png?raw=true" alt="zaincash-image"></p>

# Laravel #1 Payment Gateway For Zaincash
[![Build Status](https://travis-ci.org/firebase/php-jwt.png?branch=master)](https://travis-ci.org/firebase/php-jwt)
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
[![Maintainability](https://api.codeclimate.com/v1/badges/e6a80b17298cb4fcb56d/maintainability)](https://codeclimate.com/github/shetabit/payment/maintainability)
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>

This is a Laravel Package for Payment Gateway Integration. This package supports `Laravel 5.5+`.

## Installation
The package can be installed via Composer:
``` bash
$ composer require sindibad/zaincash
```

## Configure
If you are not using `Laravel 5.5` or higher than you need to add the provider and alias.(otherwise skip this part)

In your `config/app.php` file add these two lines.

```php
// In your providers array.
'providers' => [
    ...
    sindibad\zaincash\provider\ZaincashServiceProvider::class,
],

// In your aliases array.
'aliases' => [
    ...
    'ZainCash' => Zaincash\Payment\facades\ZainCash::class,
],
```

then run `php artisan vendor:publish` to publish `config/payment.php` file in your config directory as well as `zaincash/callback.blade.php` file in views directory.

In the config file you can set to use initial configs for all your payments. But you can also change some of these values like `eloquent_storage`,`callback_url`,`lang`,`eloquent_storage` at runtime.

Then fill the credentials for `zaincashGateway.php` file 

```php  
    "eloquent_storage" => true, // Once enabled, package will create transaction_zaincash table(through zaincash:migrate command) and fill it's values when new transaction is submitted
    "callback_url" => "default", // default, <your website domain>/[your custom segment] for callback response
    "merchant_id" => "",
    "token" => '', //Secret key for jwt encode
    "msisdn" => "",
    "lang" => "en", //ar,en
    "production" => false,//Once set to true production url's will be used otherwise test url's are consumed
    ...
]
```
## How to use

### Invoice
your `Invoice` holds your payment details, so initially we'll talk about `Invoice` class.It is mainly used for transactioninit purpose;

#### Working with invoices

before doing anything you need to use `Invoice` class to create an invoice,
Also note that if you're planning to use EloquentStorage use the following artisan command:
```php
php artisan zaincash:migrate
```

In your code, use it like the below:

```php
// At the top of the file.
use sindibad\zaincash\facades\ZainCash;
...

// Create new invoice.
$invoice = new Invoice;

// Set invoice amount.
$invoice->amount(1000);

// Set orderId if available, so you can get the value back on callback
$invoice->setOrderId($request->orderid);
// Set additional description or project type
$invoice->setServiceType("mydescription");
// As mentioned this can be set either in config file or directly in Invoice accessor 
// !Note that once you set it here this will be priority and not the config file values
$invoice->setLang("en");
// Callback url for callback response you can also set it to `default` for testing etc.
$invoice->setCallbackUrl("http://localhost:8000/customcallback");
// As mentioned before enabling EloquentStorage will create transaction_zaincash table inside your main db with transaction records
$invoice->setEnableElequentStorage(true);
// This parameter is set for main zaincash view files. it will set back button text
$invoice->setBackBtnText("back");
// Redirect url for main views back button
$invoice->setBackBtnUrl("http://localhost:8000/");
// you could also add extra parameters to invoice and get them back in callback response 
//you need to define a key for your Extra,note that you will use this key to receive your extra in callback
$invoice->appendExtra("key" , "value");

//Finally, use pay to initialize your transaction 
$pay = $invoice->pay();
//You can access redirect url for payment within ['url'] index 
$redirect_url=$pay['url'];
//Get error messages
$invoice->getErrors();
```

### callBackResponse
This method is used to retrieve gatewaycallback response.it gets jwt token  as it's input parameter.
By default, callback response input name is `token` but you can change it if source callback is different.
if token is not set in request status will set to 'cancel'

#### Working with calBackResponse

```php
$payment= ZainCash::callBackResponse();
```
`$payment` includes following parameters

- `amount`: transaction cash amount
- `status;`: success,failed,cancel,repetitious,invalid_token
- `orderId`: orderId of transaction
- `payment`: payload data
- `operationId`: operation id 
- `config`: default config settings
- `extras`: whole extras set in Invoice
- `serviceType`: service type
- `initDate`: transaction time in timestamp format
- `backButtonText`: back button text 
- `backButtonLink`: back buttonLink
- `errorMessage`: error messages if any
- `transaction`: this parameter returns when eloquent storage is set to true

you could also use getter methods for each of these parameters like below:

```php
$payment= ZainCash::callBackResponse();
$payment->getStatus();
//when you're using getExtra method remember to pass default value as second parameter even tough it is optional
$response->getExtra("user_id" , -1);
```
#### PaymentReceivedEvent
You could also use zaincash internal Event to receive payment info.
Make a new event and define`PaymentReceivedEvent::class` in your EventListener.
In your event, use it like the below:

```php
   public function handle($event)
    {
        $event->getPayment();
        //Logic
    }
```
