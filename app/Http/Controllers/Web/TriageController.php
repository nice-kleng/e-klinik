<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Triage;
use App\Services\TriageService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TriageController extends Controller
{
    protected TriageService $triageService;

    public function __construct(TriageService $triageService)
    {
        $this->triageService = $triageService;
    }

    public function create(Registration $registration): View
    {
        $registration->load(['patient', 'polyclinic', 'doctor']);

        return view('triage.create', compact('registration'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'systolic' => 'nullable|integer|min:30|max:300',
            'diastolic' => 'nullable|integer|min:20|max:200',
            'heart_rate' => 'nullable|integer|min:20|max:250',
            'respiratory_rate' => 'nullable|integer|min:4|max:80',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:10|max:300',
            'gcs' => 'nullable|integer|min:3|max:15',
            'blood_glucose' => 'nullable|integer|min:10|max:1000',
            'chief_complaint' => 'nullable|string',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'allergy_notes' => 'nullable|string',
            'fall_risk' => 'nullable|boolean',
            'nutrition_status' => 'nullable|string|max:50',
            'smoking_status' => 'nullable|string|max:50',
            'pregnancy_status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['triage_by'] = auth()->id();
        $validated['triage_at'] = now();

        if (isset($validated['weight'], $validated['height']) && $validated['height'] > 0) {
            $heightInM = $validated['height'] / 100;
            $validated['bmi'] = round($validated['weight'] / ($heightInM * $heightInM), 1);
        }

        try {
            $this->triageService->create($validated);

            return redirect()->route('queues.index')
                ->with('success', 'Data triage berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan triage: ' . $e->getMessage());
        }
    }

    public function show(Triage $triage): View
    {
        $triage->load(['registration.patient', 'registration.polyclinic', 'triageBy']);

        return view('triage.show', compact('triage'));
    }

    public function edit(Triage $triage): View
    {
        $triage->load(['registration.patient']);

        return view('triage.edit', compact('triage'));
    }

    public function update(Request $request, Triage $triage): RedirectResponse
    {
        $validated = $request->validate([
            'systolic' => 'nullable|integer|min:30|max:300',
            'diastolic' => 'nullable|integer|min:20|max:200',
            'heart_rate' => 'nullable|integer|min:20|max:250',
            'respiratory_rate' => 'nullable|integer|min:4|max:80',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:10|max:300',
            'gcs' => 'nullable|integer|min:3|max:15',
            'blood_glucose' => 'nullable|integer|min:10|max:1000',
            'chief_complaint' => 'nullable|string',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'allergy_notes' => 'nullable|string',
            'fall_risk' => 'nullable|boolean',
            'nutrition_status' => 'nullable|string|max:50',
            'smoking_status' => 'nullable|string|max:50',
            'pregnancy_status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['weight'], $validated['height']) && $validated['height'] > 0) {
            $heightInM = $validated['height'] / 100;
            $validated['bmi'] = round($validated['weight'] / ($heightInM * $heightInM), 1);
        }

        try {
            $this->triageService->update($triage, $validated);

            return redirect()->route('triage.show', $triage)
                ->with('success', 'Data triage berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui triage: ' . $e->getMessage());
        }
    }
}
