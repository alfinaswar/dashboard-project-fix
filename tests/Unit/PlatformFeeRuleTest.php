<?php

namespace Tests\Unit;

use App\Models\Platform;
use App\Models\PlatformFeeComponent;
use App\Models\PlatformFeeRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformFeeRuleTest extends TestCase
{
    use RefreshDatabase;

    private function rule(array $components): PlatformFeeRule
    {
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fastwork-'.uniqid()]);
        $rule = PlatformFeeRule::create([
            'platform_id' => $platform->id,
            'effective_from' => '2026-01-01',
        ]);
        foreach ($components as $position => [$type, $value]) {
            PlatformFeeComponent::create([
                'platform_fee_rule_id' => $rule->id,
                'type' => $type,
                'value' => $value,
                'position' => $position,
            ]);
        }

        return $rule->fresh('components');
    }

    public function test_percent_and_fixed_are_summed(): void
    {
        $rule = $this->rule([['percent', 5], ['fixed', 5000]]);

        $this->assertSame(10000, $rule->calculateFee(100000));
    }

    public function test_min_clamps_small_fee(): void
    {
        $rule = $this->rule([['percent', 5], ['min', 2500]]);

        $this->assertSame(2500, $rule->calculateFee(10000));
        $this->assertSame(5000, $rule->calculateFee(100000));
    }

    public function test_max_clamps_large_fee(): void
    {
        $rule = $this->rule([['percent', 10], ['max', 50000]]);

        $this->assertSame(50000, $rule->calculateFee(1000000));
        $this->assertSame(10000, $rule->calculateFee(100000));
    }

    public function test_rule_is_selected_by_effective_date(): void
    {
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);
        $old = PlatformFeeRule::create(['platform_id' => $platform->id, 'effective_from' => '2026-01-01', 'effective_to' => '2026-05-31']);
        PlatformFeeComponent::create(['platform_fee_rule_id' => $old->id, 'type' => 'percent', 'value' => 5]);
        $new = PlatformFeeRule::create(['platform_id' => $platform->id, 'effective_from' => '2026-06-01']);
        PlatformFeeComponent::create(['platform_fee_rule_id' => $new->id, 'type' => 'percent', 'value' => 8]);

        $this->assertSame($old->id, $platform->feeRuleFor('2026-03-01')?->id);
        $this->assertSame($new->id, $platform->feeRuleFor('2026-07-01')?->id);
        $this->assertNull($platform->feeRuleFor('2025-12-31'));
    }

    public function test_platform_falls_back_to_legacy_formula_without_rules(): void
    {
        $platform = Platform::create(['name' => 'Lama', 'code' => 'lama-'.uniqid(), 'commission_percent' => 5, 'fixed_fee' => 1000]);

        $this->assertNull($platform->feeRuleFor('2026-01-01'));
        $this->assertSame(6000, $platform->feeFor(100000, '2026-01-01'));
    }
}
