<?php

namespace App\Services;

use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterService
{
    public function sickLeave(Registration $registration)
    {
        $summary = $registration->summary;
        if (!$summary || !$summary->sick_leave_days) {
            abort(404, 'Resume kunjungan atau data cuti sakit tidak ditemukan');
        }

        return Pdf::loadView('letters.sick-leave', compact('registration', 'summary'));
    }

    public function healthCertificate(Registration $registration)
    {
        $mr = $registration->medicalRecords()->latest()->first();
        $summary = $registration->summary;

        return Pdf::loadView('letters.health-certificate', compact('registration', 'mr', 'summary'));
    }

    public function referral(Registration $registration)
    {
        $mr = $registration->medicalRecords()->latest()->first();
        $summary = $registration->summary;

        return Pdf::loadView('letters.referral', compact('registration', 'mr', 'summary'));
    }

    public function medicalCertificate(Registration $registration)
    {
        $mr = $registration->medicalRecords()->latest()->first();
        $summary = $registration->summary;

        return Pdf::loadView('letters.medical-certificate', compact('registration', 'mr', 'summary'));
    }
}
