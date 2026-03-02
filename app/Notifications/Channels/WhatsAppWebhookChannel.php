<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class WhatsAppWebhookChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $url = (string) config('crm.notifications.whatsapp_webhook_url');
        $enabled = (bool) config('crm.notifications.whatsapp_enabled');

        if (! $enabled || $url === '') {
            return;
        }

        if (! method_exists($notification, 'toWhatsAppWebhook')) {
            return;
        }

        $payload = $notification->toWhatsAppWebhook($notifiable);

        if (! is_array($payload)) {
            return;
        }

        if (empty($payload['to'])) {
            return;
        }

        Http::timeout(8)->post($url, $payload);
    }
}
