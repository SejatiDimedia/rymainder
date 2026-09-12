<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Digest Notification
    |--------------------------------------------------------------------------
    |
    | The email address where daily reminder summaries will be sent.
    |
    */
    'admin_digest_email' => env('ADMIN_DIGEST_EMAIL', 'admin@yayasan.org'),

    /*
    |--------------------------------------------------------------------------
    | Channels Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for all notification channels: WhatsApp, Telegram, Email, SMS.
    |
    */
    'channels' => [
        'whatsapp' => [
            'driver' => env('WHATSAPP_DRIVER', 'cloud_api'), // 'cloud_api' or 'provider'
            'cloud_api' => [
                'token' => env('WHATSAPP_CLOUD_API_TOKEN'),
                'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
                'endpoint' => 'https://graph.facebook.com/v20.0',
            ],
            'provider' => [
                'api_key' => env('WHATSAPP_PROVIDER_API_KEY'),
                'endpoint' => env('WHATSAPP_PROVIDER_ENDPOINT'),
            ],
        ],

        'telegram' => [
            'bot_token' => env('TELEGRAM_BOT_TOKEN'),
            'bot_username' => env('TELEGRAM_BOT_USERNAME'),
            'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
            'endpoint' => 'https://api.telegram.org',
        ],

        'sms' => [
            'enabled' => env('SMS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'twilio'),
            'api_key' => env('SMS_API_KEY'),
            'api_secret' => env('SMS_API_SECRET'),
            'from' => env('SMS_FROM'),
        ],
    ],
];
