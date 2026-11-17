<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformFeeRule extends Model
{
    protected $fillable = ['platform_id', 'effective_from', 'effective_to', 'is_active', 'note'];

    protected $casts = ['effective_from' => 'date', 'effective_to' => 'date', 'is_active' => 'boolean'];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(PlatformFeeComponent::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Fee = clamp(sum(percent% * gross) + sum(fixed), min, max)
     * min/max opsional, tidak diterapkan kalau komponen tidak ada.
     */
    public function calculateFee(int $grossAmount): int
    {
        $components = $this->relationLoaded('components') ? $this->components : $this->components()->get();

        $total = 0.0;
        $min = null;
        $max = null;

        foreach ($components as $component) {
            $value = (float) $component->value;
            match ($component->type) {
                'percent' => $total += $grossAmount * $value / 100,
                'fixed' => $total += $value,
                'min' => $min = $min === null ? $value : max($min, $value),
                'max' => $max = $max === null ? $value : min($max, $value),
                default => null,
            };
        }

        if ($min !== null) {
            $total = max($total, $min);
        }
        if ($max !== null) {
            $total = min($total, $max);
        }

        return (int) round($total);
    }

    /**
     * Tarif yang berlaku pada tanggal tertentu (effective_to null = berlaku terus).
     */
    public static function activeFor(int $platformId, string $date): ?self
    {
        return static::with('components')
            ->where('platform_id', $platformId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date))
            ->orderByDesc('effective_from')
            ->first();
    }
}
