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

    // ── WhatsApp Gateway ─────────────────────────────────────────────────────
    // Set WHATSAPP_DRIVER to: log (default), baileys, fonnte, wablas, or custom
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'baileys_url' => env('BAILEYS_URL', 'http://127.0.0.1:3001'),

        // Fonnte.com
        'fonnte_token' => env('WHATSAPP_FONNTE_TOKEN'),

        // Wablas.com
        'wablas_token' => env('WHATSAPP_WABLAS_TOKEN'),
        'wablas_domain' => env('WHATSAPP_WABLAS_DOMAIN', 'texas.wablas.com'),

        // Custom provider
        'custom_url' => env('WHATSAPP_CUSTOM_URL'),
        'custom_token' => env('WHATSAPP_CUSTOM_TOKEN'),
    ],

    // ── Payment Gateway ───────────────────────────────────────────────────────
    // Set PAYMENT_GATEWAY_DRIVER to: sandbox (default) or midtrans
    'payment_gateway' => [
        'driver' => env('PAYMENT_GATEWAY_DRIVER', 'sandbox'),
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        ],
    ],

];
