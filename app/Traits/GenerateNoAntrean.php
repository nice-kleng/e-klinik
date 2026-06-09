<?php

namespace App\Traits;

use App\Models\Queue;
use App\Models\Polyclinic;

trait GenerateNoAntrean
{
    protected function generateNoAntrean(Polyclinic $polyclinic, string $date): string
    {
        $prefix = $polyclinic->code;
        $count = Queue::where('polyclinic_id', $polyclinic->id)
            ->where('queue_date', $date)
            ->count();

        return sprintf('%s-%03d', $prefix, $count + 1);
    }
}
