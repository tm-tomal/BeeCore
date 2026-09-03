<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | BeeCore hosted payments (bKash Tokenized Checkout)
    |--------------------------------------------------------------------------
    | The callback URL must be HTTPS and registered in the bKash merchant
    | portal. Production normally uses APP_URL, but when the app runs behind
    | a tunnel or a different public domain than APP_URL you can override the
    | exact callback with BEE_PAY_CALLBACK_URL (e.g. https://pay.example.com/bee-pay/bkash/callback).
    */
    'beecore' => [
        'callback_url' => env('BEE_PAY_CALLBACK_URL'),
    ],

];
