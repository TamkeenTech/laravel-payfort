<?php

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
