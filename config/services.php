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

    'microsoft' => [
        'tenant_id' => env('MICROSOFT_TENANT_ID'),
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect_uri' => env('MICROSOFT_REDIRECT_URI'),
        'scopes' => explode(' ', env('MICROSOFT_SCOPES', 'openid profile email User.Read')),
        'ca_bundle' => env('MICROSOFT_CA_BUNDLE'),
    ],

    'graph_mail' => [
        'enabled' => filter_var(env('GRAPH_MAIL_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'tenant_id' => env('GRAPH_MAIL_TENANT_ID', env('MS_GRAPH_TENANT_ID')),
        'client_id' => env('GRAPH_MAIL_CLIENT_ID', env('MS_GRAPH_CLIENT_ID')),
        'client_secret' => env('GRAPH_MAIL_CLIENT_SECRET', env('MS_GRAPH_CLIENT_SECRET')),
        'from_address' => env('GRAPH_MAIL_FROM_ADDRESS'),
        'from_name' => env('GRAPH_MAIL_FROM_NAME', 'Nere Tools'),
        'save_to_sent_items' => filter_var(env('GRAPH_MAIL_SAVE_TO_SENT_ITEMS', true), FILTER_VALIDATE_BOOLEAN),
        'timeout' => (int) env('GRAPH_MAIL_TIMEOUT', 15),
        'connect_timeout' => (int) env('GRAPH_MAIL_CONNECT_TIMEOUT', 10),
        'token_cache_key' => env('GRAPH_MAIL_TOKEN_CACHE_KEY', 'nere_tools_graph_mail_token'),
        'test_recipient' => env('GRAPH_MAIL_TEST_RECIPIENT'),
    ],

];
