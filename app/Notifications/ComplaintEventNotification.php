<?php

namespace App\Notifications;

use App\Models\Complaint;
use App\Notifications\Channels\WhatsAppWebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Complaint $complaint,
        public string $event,
        public string $message
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        $channels = ['mail'];

        if (config('crm.notifications.whatsapp_enabled')) {
            $channels[] = WhatsAppWebhookChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject("[CRM Complaint] {$this->complaint->ticket_number} - {$this->event}")
            ->line($this->message)
            ->line("Ticket: {$this->complaint->ticket_number}")
            ->line("Customer: {$this->complaint->customer_name}")
            ->line("Brand: ".($this->complaint->brand?->name ?? '-'))
            ->line("Status: {$this->complaint->status}");

        if (! empty($this->complaint->current_pool_department)) {
            $mail->line("Pool Department: {$this->complaint->current_pool_department}");
        }

        if (! empty($this->complaint->action_type)) {
            $mail->line("Action Type: {$this->complaint->action_type}");
        }

        if ($this->event === 'ticket_closed_summary') {
            $mail
                ->line('Resolution Summary: '.($this->complaint->resolution_summary ?: '-'))
                ->line('CAPA Root Cause: '.($this->complaint->capa_root_cause ?: '-'))
                ->line('CAPA Corrective Action: '.($this->complaint->capa_corrective_action ?: '-'))
                ->line('CAPA Preventive Action: '.($this->complaint->capa_preventive_action ?: '-'));
        }

        return $mail->action('Lihat Complaint', route('complaints.show', $this->complaint));
    }

    /**
     * @return array<string, mixed>
     */
    public function toWhatsAppWebhook(object $notifiable): array
    {
        return [
            'to' => $notifiable->phone,
            'message' => implode("\n", [
                '[CRM Complaint]',
                $this->message,
                "Ticket: {$this->complaint->ticket_number}",
                "Customer: {$this->complaint->customer_name}",
                "Status: {$this->complaint->status}",
            ]),
            'event' => $this->event,
        ];
    }
}
