<?php

namespace Tests\Unit;

use App\Models\Platform;
use PHPUnit\Framework\TestCase;

class PlatformFeeTest extends TestCase
{
    public function test_platform_fee_uses_commission_and_fixed_fee(): void
    {
        $platform = new Platform([
            'commission_percent' => 20,
            'fixed_fee' => 5000,
        ]);

        $this->assertSame(205000, $platform->calculateFee(1000000));
        $this->assertSame(790000, $platform->calculateNet(1000000, 5000));
    }

    public function test_platform_fee_rounds_commission_to_nearest_rupiah(): void
    {
        $platform = new Platform([
            'commission_percent' => 12.5,
            'fixed_fee' => 0,
        ]);

        $this->assertSame(125, $platform->calculateFee(999));
    }
}
