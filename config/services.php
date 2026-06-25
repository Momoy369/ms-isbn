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

    'rajaongkir' => [
        'key' => env('RAJAONGKIR_API_KEY'),
        'origin_city_id' => env('RAJAONGKIR_ORIGIN_CITY_ID', '1585'),
        'verify_ssl' => env('RAJAONGKIR_VERIFY_SSL', true),
    ],

    'ipaymu' => [
        'api_key' => env('IPAYMU_API_KEY'),
        'va' => env('IPAYMU_VA'),
        'base_url' => env('IPAYMU_BASE_URL', 'https://my.ipaymu.com/api/v2'),
        'verify_ssl' => env('IPAYMU_VERIFY_SSL', true),
        'callback_secret' => env('IPAYMU_CALLBACK_SECRET'),
        'verify_callback_signature' => env('IPAYMU_VERIFY_SIGNATURE', true),
    ],

    'perpusnas' => [
        'base_url' => env('PERPUSNAS_API_BASE_URL', 'https://api-penerbitsakedap.perpusnas.go.id'),
        'token' => env('PERPUSNAS_API_TOKEN'),
        'username' => env('PERPUSNAS_API_USERNAME'),
        'password' => env('PERPUSNAS_API_PASSWORD'),
        'verify_ssl' => env('PERPUSNAS_VERIFY_SSL', true),
    ],

    'reminder' => [
        'email_enabled' => env('REMINDER_EMAIL_ENABLED', false),
        'whatsapp_enabled' => env('REMINDER_WHATSAPP_ENABLED', false),
        'sms_enabled' => env('REMINDER_SMS_ENABLED', false),
        'whatsapp_webhook_url' => env('REMINDER_WHATSAPP_WEBHOOK_URL'),
        'sms_webhook_url' => env('REMINDER_SMS_WEBHOOK_URL'),
    ],

];
