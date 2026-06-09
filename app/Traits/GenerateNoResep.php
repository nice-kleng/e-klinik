<?php

namespace App\Traits;

use App\Models\Prescription;

trait GenerateNoResep
{
    protected function generateNoResep(): string
    {
        $year = now()->year;
        $last = Prescription::where('prescription_number', 'like', "RCP-{$year}-%")
            ->orderBy('id', 'desc')
            ->value('prescription_number');

        if ($last) {
            $num = (int) substr($last, -4) + 1;
        } else {
            $num = 1;
        }

        return sprintf('RCP-%s-%04d', $year, $num);
    }
}
