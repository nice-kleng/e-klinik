<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Services\LetterService;

class LetterController extends Controller
{
    protected LetterService $letterService;

    public function __construct(LetterService $letterService)
    {
        $this->letterService = $letterService;
    }

    public function sickLeave(Registration $registration)
    {
        return $this->letterService->sickLeave($registration)->stream('surat-sakit.pdf');
    }

    public function healthCertificate(Registration $registration)
    {
        return $this->letterService->healthCertificate($registration)->stream('surat-sehat.pdf');
    }

    public function referral(Registration $registration)
    {
        return $this->letterService->referral($registration)->stream('surat-rujukan.pdf');
    }

    public function medicalCertificate(Registration $registration)
    {
        return $this->letterService->medicalCertificate($registration)->stream('surat-keterangan-medis.pdf');
    }
}
