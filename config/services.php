<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'qontak' => [
        'base_url' => env('QONTAK_BASE_URL', 'https://service-chat.qontak.com/api/open/v1'),
        'bearer_token' => env('QONTAK_BEARER_TOKEN'),
        'channel_integration_id' => env('QONTAK_CHANNEL_INTEGRATION_ID', '3702ae75-4d97-482c-969a-49f19254c418'),
        'message_template_id' => env('QONTAK_MESSAGE_TEMPLATE_ID'),
    ],

];
