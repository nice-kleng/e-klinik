<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function formatDate($date, string $format = 'd/m/Y'): ?string
    {
        if (empty($date)) return null;
        return Carbon::parse($date)->format($format);
    }

    public static function formatDateTime($date, string $format = 'd/m/Y H:i'): ?string
    {
        if (empty($date)) return null;
        return Carbon::parse($date)->format($format);
    }

    public static function formatToSql($date): ?string
    {
        if (empty($date)) return null;
        return Carbon::parse($date)->format('Y-m-d');
    }

    public static function indonesianDate($date): ?string
    {
        if (empty($date)) return null;

        $months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $d = Carbon::parse($date);
        return $d->day . ' ' . $months[$d->month - 1] . ' ' . $d->year;
    }

    public static function age($birthDate): ?int
    {
        if (empty($birthDate)) return null;
        return Carbon::parse($birthDate)->age;
    }

    public static function daysUntil($date): ?int
    {
        if (empty($date)) return null;
        return (int) Carbon::now()->startOfDay()->diffInDays(Carbon::parse($date)->startOfDay(), false);
    }
}
