<?php

namespace App\Enums;

enum VisitType: string
{
    case RAWAT_JALAN = 'rawat_jalan';
    case GAWAT_DARURAT = 'gawat_darurat';
    case RAWAT_INAP = 'rawat_inap';

    public function label(): string
    {
        return match ($this) {
            self::RAWAT_JALAN => 'Rawat Jalan',
            self::GAWAT_DARURAT => 'Gawat Darurat',
            self::RAWAT_INAP => 'Rawat Inap',
        };
    }
}
