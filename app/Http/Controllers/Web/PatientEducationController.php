<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\PatientEducation;
use App\Services\EducationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientEducationController extends Controller
{
    protected EducationService $educationService;

    public function __construct(EducationService $educationService)
    {
        $this->educationService = $educationService;
    }

    public function create(MedicalRecord $medicalRecord): View
    {
        $medicalRecord->load(['patient', 'doctor', 'polyclinic', 'diagnoses.icd10Diagnosis']);

        return view('patient-education.create', compact('medicalRecord'));
    }

    public function store(Request $request, MedicalRecord $medicalRecord): RedirectResponse
    {
        $validated = $request->validate([
            'diagnosis_explained' => 'nullable|string',
            'medication_instructions' => 'nullable|string',
            'diet_instructions' => 'nullable|string',
            'activity_instructions' => 'nullable|string',
            'follow_up_plan' => 'nullable|string',
            'education_date' => 'required|date',
        ]);

        $validated['educator_id'] = auth()->id();

        try {
            $this->educationService->create($medicalRecord, $validated);

            return redirect()->route('medical-records.show', $medicalRecord)
                ->with('success', 'Edukasi pasien berhasil dicatat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan edukasi: ' . $e->getMessage());
        }
    }

    public function edit(MedicalRecord $medicalRecord): View
    {
        $education = $medicalRecord->education;

        if (!$education) {
            return redirect()->route('education.create', $medicalRecord)
                ->with('error', 'Belum ada data edukasi untuk rekam medis ini');
        }

        return view('patient-education.edit', compact('medicalRecord', 'education'));
    }

    public function update(Request $request, MedicalRecord $medicalRecord): RedirectResponse
    {
        $education = $medicalRecord->education;

        if (!$education) {
            return redirect()->route('education.create', $medicalRecord)
                ->with('error', 'Belum ada data edukasi untuk rekam medis ini');
        }

        $validated = $request->validate([
            'diagnosis_explained' => 'nullable|string',
            'medication_instructions' => 'nullable|string',
            'diet_instructions' => 'nullable|string',
            'activity_instructions' => 'nullable|string',
            'follow_up_plan' => 'nullable|string',
            'education_date' => 'required|date',
        ]);

        try {
            $this->educationService->update($education, $validated);

            return redirect()->route('medical-records.show', $medicalRecord)
                ->with('success', 'Edukasi pasien berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui edukasi: ' . $e->getMessage());
        }
    }
}
