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

    // n8n Webhook Configuration
    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL', 'http://n8n:5678'),
        'webhook_secret' => env('N8N_WEBHOOK_SECRET'),
        'chat_webhook_url' => env('N8N_CHAT_WEBHOOK_URL', 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d'),
        'agent_webhook_url' => env('N8N_AGENT_WEBHOOK_URL', 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d'),
        'ai_analysis_webhook_url' => env('N8N_AI_ANALYSIS_WEBHOOK_URL', 'https://n8n.getaxia.de/webhook/ai-analysis'),
    ],

    'mcp' => [
        'shared_secret' => env('MCP_SHARED_SECRET'),
        'server_url' => env('MCP_SERVER_URL', 'http://mcp-axia:8102'),
    ],

];
