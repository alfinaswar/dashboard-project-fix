<?php

use App\Models\Setting;
use Carbon\Carbon;

if (!function_exists('format_rupiah')) {
    /**
     * Format number to Rupiah currency string.
     *
     * @param int|float|null $amount
     * @param bool $withPrefix
     * @return string
     */
    function format_rupiah($amount, bool $withPrefix = true): string
    {
        $amount = (int) round($amount ?? 0);
        $formatted = number_format($amount, 0, ',', '.');
        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('setting')) {
    /**
     * Get or set setting values.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return app(Setting::class);
        }

        return Setting::get($key, $default);
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date in Asia/Jakarta timezone.
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    function format_date($date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->timezone('Asia/Jakarta')->translatedFormat($format);
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format datetime in Asia/Jakarta timezone.
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    function format_datetime($date, string $format = 'd/m/Y H:i'): string
    {
        if (!$date) {
            return '-';
        }

        try {
            return Carbon::parse($date)->timezone('Asia/Jakarta')->translatedFormat($format);
        } catch (\Exception $e) {
            return (string) $date;
        }
    }
}
