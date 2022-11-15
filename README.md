Laravel Payfort
=======================
[![Latest Version on Packagist](https://img.shields.io/packagist/v/tamkeen-tech/laravel-payfort.svg?style=flat-square)](https://packagist.org/packages/tamkeen-tech/laravel-payfort)
[![Total Downloads](https://img.shields.io/packagist/dt/tamkeen-tech/laravel-payfort.svg?style=flat-square)](https://packagist.org/packages/tamkeen-tech/laravel-payfort)

Helps you integrate Payfort into your application. currently it supports `Custom merchant page integration` refer to this [link](https://paymentservices-reference.payfort.com/docs/api/build/index.html#custom-merchant-page-integration) to understand more, also this package support using multiple merchant accounts.

Currently this package supports the below operation list:
- AUTHORIZATION/PURCHASE
- TOKENIZATION
- CAPTURE
- REFUND
- INSTALLMENTS
- VOID
- CHECK_STATUS

Please make sure to read and understand `payfort` documentation.

Currently it supports only Laravel 9.

## Installation
You need to run this command
```bash
composer require tamkeen-tech/laravel-payfort
```
To publish the configurations please run this command
```bash
php artisan vendor:publish --tag payfort-config
```
This will generate a `config/payfort.php` with the default configurations

```php
return [
    'gateway_host' => env('PAYFORT_GATEWAY_HOST', 'https://checkout.payfort.com/'),
    'gateway_sandbox_host' => env('PAYFORT_GATEWAY_SAND_BOX_HOST', 'https://sbcheckout.payfort.com/'),

    'merchants' => [
        'default' => [
            'merchant_identifier' => env('PAYFORT_MERCHANT_IDENTIFIER', null),
            'access_code' => env('PAYFORT_ACESS_CODE', null),
            'SHA_request_phrase' => env('PAYFORT_SHAR_REQUEST_PHARSE', null),
            'SHA_response_phrase' => env('PAYFORT_SHAR_RESPONSE_PHRASE', null),
        ],
    ],

    'sandbox_mode' => env('PAYFORT_SANDBOX_MODE', true),
    'SHA_type' => env('PAYFORT_SHA_TYPE', 'sha256'),
    'language' => env('PAYFORT_LANGUAGE', 'en'),
];
```
### Then you can update your `.env` file to have the correct credentials:
```bash
PAYFORT_SANDBOX_MODE=true                     # Defines wether to activate the payfort sandbox enviroment or not.
PAYFORT_MERCHANT_IDENTIFIER=test              # The payfort merchant account identifier
PAYFORT_ACCESS_CODE=test                      # The payfort account access code
PAYFORT_SHA_TYPE=sha256                       # The payfort account sha type. sha256/sha512
PAYFORT_SHA_REQUEST_PHRASE=test               # The payfort account sha request phrase
PAYFORT_SHA_RESPONSE_PHRASE=test              # The payfort account sha response phrase
```

## Usage
Once you identified your credentials and configurations, your are ready to use payfort operations.

### Tokenization request:
To display tokenization page, in your controller method you can add the following
```php
Payfort::tokenization(
    1000, # Bill amount
    'redirect_url', # the recirect to url after tokenization
    true # either to return form html or not (optional)
);
```
### Authorization/Purchase:
To send a purchase or authorization command, in your controller on the return of the tokenization request from payfort add this code
```php
$response = Payfort::purchase(
    [],  # Request body coming from the tokenization
    100, # Bill amount
    'test@test.ts', # User email
    'redirect_url', # The return back url after purchase
    [] # installment data (optional)
);
```

```php
$response = Payfort::authorize(
    [],  # Request body coming from the tokenization
    100, # Bill amount
    'test@test.ts', # User email
    'redirect_url' # The return back url after purchase
);
```

To handle the 3Ds redirection, you can use this code snippet:
```php
if ($response->should3DsRedirect()) {
    return redirect()->away($response->get3DsUri());
}
```
Where `$response` is the response coming from the purchase or the authorization.

if the transaction is done successfully you can get the transaction fort id by using this:
```php
$response->getResponseFortId();
```

or the used payment method by this:
```php
$response->getResponsePaymentMethod()
```

### Process response
To process the response coming from payfort and to make sure it's valid you can use the following code snippet:
```php
Payfort::processResponse(
    [] # the response array
);
```
it will throw exception `\TamkeenTech\Payfort\Exceptions\PaymentFailed`, if the response is not valid.

if the transaction is done successfully you can get the transaction fort id by using this:
```php
$response->getResponseFortId();
```

or the used payment method by this:
```php
$response->getResponsePaymentMethod()
```

### Capture
Used only after authorization, to send a capture command use code below:
```php
Payfort::capture(
    'fort_id', # fort id for the payment transaction
    100.0 # bill amount
);
```

### Void
Used only after authorization, to send a void command use code below:
```php
Payfort::void(
    'fort_id' # fort id for the payment transaction
);
```

### Refund
Used only after purchase, to send a refund command use the code below:
```php
Payfort::refund(
    'fort_id', # fort id for the payment transaction
    1000 # amount to be reunded must not exceed the bill amount
);
```

### Merchant extra
Payfort support sending extra fields to the request and they will be returned back to you on the response, so to add merchant extras to any command, you do the following:
```php
Payfort::setMerchantExtra('test')->tokenization(
    1000, # Bill amount
    'redirect_url', # the recirect to url after tokenization
    true # either to return form html or not (optional)
);
```

you can use this method `setMerchantExtra` before any command you want, and you have max 5 extras to add.


## Logging
To log your requests with payfort you can listen to this event `\TamkeenTech\Payfort\Events\PayfortMessageLog` it will contain the data sent and the resposne

This is an example on how it can be used:
```php
$log = app(PayfortLog::class);

$log->contract_id = data_get($event->request, 'merchant_extra', data_get($event->response, 'merchant_extra', null));

if (isset($event->response['card_number'])) {
    $last_four_digits = substr($event->response['card_number'], -4);
    $log->card_number = '************'.$last_four_digits;
}

if (isset($event->response['amount'])) {
    $log->amount = floatval($event->response['amount'] / 100);
}

if (isset($event->response['response_message'])) {
    $log->response_message = data_get($event->response, 'response_message');
}

if (isset($event->response['merchant_reference'])) {
    $log->merchant_reference = $event->response['merchant_reference'];
}

if (isset($event->request['merchant_reference'])) {
    $log->merchant_reference = $event->request['merchant_reference'];
}

$log->fort_id = data_get($event->response, 'fort_id');
$log->payment_option = data_get($event->response, 'payment_option');
$log->command = data_get($event->response, 'command', data_get($event->response, 'service_command'));
$log->response_code = data_get($event->response, 'response_code');

$log->request = $event->request ? json_encode($event->request) : "";
$log->response = json_encode($event->response);

$log->save();
```

## License

The MIT License (MIT). Please see License File for more information.