<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:remind';
    protected $description = 'Tandai pengingat masa aktif langganan yang jatuh tempo';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $count = 0;
        Subscription::whereDate('end_date', '>=', $today)->whereDate('end_date', '<=', $today->copy()->addDays(30))->chunkById(100, function ($subscriptions) use ($today, &$count) {
            foreach ($subscriptions as $subscription) {
                $days = $today->diffInDays($subscription->end_date, false);
                $column = match ($days) {
                    30 => 'reminder_30_sent_at',
                    14 => 'reminder_14_sent_at',
                    7 => 'reminder_7_sent_at',
                    default => null,
                };
                if ($column && !$subscription->{$column}) {
                    $subscription->update([$column => now()]);
                    User::query()->first()?->notify(new SubscriptionReminderNotification($subscription->load(['client', 'servicePackage']), $days));
                    $count++;
                }
            }
        });
        Subscription::whereDate('end_date', '<', $today)->whereNull('expired_notified_at')->chunkById(100, function ($subscriptions) use (&$count) {
            foreach ($subscriptions as $subscription) {
                $subscription->update(['expired_notified_at' => now()]);
                User::query()->first()?->notify(new SubscriptionReminderNotification($subscription->load(['client', 'servicePackage']), 0));
                $count++;
            }
        });
        $this->info("{$count} pengingat ditandai.");
        return self::SUCCESS;
    }
}
