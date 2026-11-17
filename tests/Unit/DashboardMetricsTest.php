<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class DashboardMetricsTest extends TestCase
{
    public function test_net_amount_is_summed_only_for_completed_orders(): void
    {
        $orders = collect([
            (object) ['status' => 'completed', 'net_amount' => 800000],
            (object) ['status' => 'in_progress', 'net_amount' => 500000],
        ]);

        $this->assertSame(800000, $orders->where('status', 'completed')->sum('net_amount'));
    }
}
