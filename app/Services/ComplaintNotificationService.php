<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Notifications\ComplaintEventNotification;
use Illuminate\Support\Facades\Notification;

class ComplaintNotificationService
{
    public function notifyConfiguredRecipientsForIncomingComplaint(Complaint $complaint): void
    {
        $emails = NotificationRecipient::query()
            ->where('event_key', NotificationRecipient::EVENT_COMPLAINT_CREATED)
            ->where('is_active', true)
            ->pluck('email')
            ->unique()
            ->filter()
            ->values();

        foreach ($emails as $email) {
            Notification::route('mail', $email)->notify(
                new ComplaintEventNotification(
                    complaint: $complaint,
                    event: 'complaint_created',
                    message: 'Complaint baru masuk dan membutuhkan tindak lanjut.',
                )
            );
        }
    }

    public function notifyDepartmentUsers(Complaint $complaint, string $department, string $event, string $message): void
    {
        $users = User::query()
            ->where('is_active', true)
            ->where('department', $department)
            ->whereNotNull('email')
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new ComplaintEventNotification(
            complaint: $complaint,
            event: $event,
            message: $message,
        ));
    }

    public function notifyMarketingOnClosed(Complaint $complaint): void
    {
        $users = User::query()
            ->where('is_active', true)
            ->where('department', User::DEPT_MARKETING)
            ->whereNotNull('email')
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new ComplaintEventNotification(
            complaint: $complaint,
            event: 'ticket_closed_summary',
            message: 'Tiket complaint sudah closed. Ringkasan dan CAPA final terlampir di detail tiket.',
        ));
    }
}
