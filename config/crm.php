<?php

return [
    'notifications' => [
        'whatsapp_enabled' => env('CRM_WHATSAPP_ENABLED', false),
        'whatsapp_webhook_url' => env('CRM_WHATSAPP_WEBHOOK_URL', ''),
    ],
];
