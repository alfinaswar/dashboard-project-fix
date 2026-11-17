<?php

namespace Tests\Unit;

use App\Models\Subscription;
use Carbon\Carbon;
use Tests\TestCase;

class SubscriptionStatusTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_subscription_is_expiring_at_thirty_days(): void
    {
        Carbon::setTestNow('2026-11-17');
        $subscription = new Subscription(['status' => 'active', 'end_date' => '2026-12-17']);

        $this->assertSame('expiring', $subscription->calculated_status);
    }

    public function test_frozen_subscription_stays_frozen_after_expiry(): void
    {
        Carbon::setTestNow('2026-11-17');
        $subscription = new Subscription(['status' => 'frozen', 'end_date' => '2026-11-01']);

        $this->assertSame('frozen', $subscription->calculated_status);
    }
}
