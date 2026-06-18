<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\VisitSummary;
use App\Services\VisitSummaryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitSummaryController extends Controller
{
    protected VisitSummaryService $summaryService;

    public function __construct(VisitSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function create(Registration $registration): View
    {
        $registration->load(['patient', 'polyclinic', 'doctor', 'medicalRecords.diagnoses.icd10Diagnosis']);

        $mr = $registration->medicalRecords()->latest()->first();

        return view('visit-summary.create', compact('registration', 'mr'));
    }

    public function store(Request $request, Registration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'final_diagnosis' => 'nullable|string',
            'discharge_status' => 'required|in:sembuh,dirujuk,pulang_paksa,meninggal,lainnya',
            'follow_up_plan' => 'nullable|string',
            'referral_notes' => 'nullable|string',
            'referral_to' => 'nullable|string|max:255',
            'sick_leave_days' => 'nullable|integer|min:0|max:365',
            'sick_leave_from' => 'nullable|date',
            'sick_leave_to' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $summary = $this->summaryService->create($registration, $validated);

            return redirect()->route('visit-summary.show', $summary)
                ->with('success', 'Resume kunjungan berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan resume: ' . $e->getMessage());
        }
    }

    public function show(VisitSummary $visitSummary): View
    {
        $visitSummary->load(['registration.patient', 'registration.polyclinic', 'registration.doctor', 'creator']);

        return view('visit-summary.show', compact('visitSummary'));
    }
}
