<?php

namespace App\Enums;

enum QueueStatus: string
{
    case WAITING = 'waiting';
    case CALLED = 'called';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'Menunggu',
            self::CALLED => 'Dipanggil',
            self::IN_PROGRESS => 'Dalam Pemeriksaan',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
