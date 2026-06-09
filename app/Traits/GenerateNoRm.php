<?php

namespace App\Traits;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;

trait GenerateNoRm
{
    protected function generateNoRm(): string
    {
        $year = now()->year;
        $last = Patient::where('no_rm', 'like', "RM-{$year}-%")
            ->orderBy('id', 'desc')
            ->value('no_rm');

        if ($last) {
            $num = (int) substr($last, -4) + 1;
        } else {
            $num = 1;
        }

        return sprintf('RM-%s-%04d', $year, $num);
    }
}
