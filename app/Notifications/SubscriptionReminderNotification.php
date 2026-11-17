<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Subscription $subscription, public int $days) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Langganan akan berakhir',
            'message' => "Langganan {$this->subscription->billing_type_label} untuk {$this->subscription->client?->name} berakhir dalam {$this->days} hari.",
            'subscription_id' => $this->subscription->id,
        ];
    }
}
