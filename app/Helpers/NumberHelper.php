<?php

namespace App\Helpers;

class NumberHelper
{
    public static function formatRupiah($amount): string
    {
        if ($amount === null || $amount === '') return 'Rp 0';
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    public static function formatNumber($number, int $decimals = 0): string
    {
        if ($number === null) return '0';
        return number_format((float) $number, $decimals, ',', '.');
    }

    public static function parseRupiah(string $rupiah): float
    {
        return (float) str_replace(['Rp ', '.', ','], ['', '', '.'], $rupiah);
    }

    public static function percent(float $value, float $total, int $decimals = 1): string
    {
        if ($total == 0) return '0%';
        return number_format(($value / $total) * 100, $decimals) . '%';
    }
}
