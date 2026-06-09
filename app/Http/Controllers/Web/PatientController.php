<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(Request $request): View
    {
        $query = Patient::with(['bpjsPatient', 'creator']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('nik', 'like', "%{$keyword}%")
                    ->orWhere('no_rm', 'like', "%{$keyword}%");
            });
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create(): View
    {
        return view('patients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_rm' => 'nullable|string|unique:patients,no_rm',
            'nik' => 'required|string|size:16|unique:patients,nik',
            'no_kk' => 'nullable|string',
            'name' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:L,P',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'address' => 'nullable|string',
            'rt' => 'nullable|string',
            'rw' => 'nullable|string',
            'village' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:255',
            'marriage_status' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'insurance_type' => 'nullable|string|max:50',
            'insurance_number' => 'nullable|string|max:50',
        ]);

        try {
            $this->patientService->register($validated);

            return redirect()->route('patients.index')
                ->with('success', 'Pasien berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan pasien: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan pasien: ' . $e->getMessage());
        }
    }

    public function show(Patient $patient): View
    {
        $patient->load(['bpjsPatient', 'creator']);
        $visitHistory = $this->patientService->getVisitHistory($patient);

        return view('patients.show', compact('patient', 'visitHistory'));
    }

    public function edit(Patient $patient): View
    {
        $patient->load('bpjsPatient');

        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => 'required|string|size:16|unique:patients,nik,' . $patient->id,
            'no_kk' => 'nullable|string',
            'name' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'required|date',
            'gender' => 'required|in:L,P',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'address' => 'nullable|string',
            'rt' => 'nullable|string',
            'rw' => 'nullable|string',
            'village' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:255',
            'marriage_status' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'insurance_type' => 'nullable|string|max:50',
            'insurance_number' => 'nullable|string|max:50',
        ]);

        try {
            $patient->update($validated);

            return redirect()->route('patients.show', $patient)
                ->with('success', 'Data pasien berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui pasien: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data pasien');
        }
    }

    public function destroy(Patient $patient): RedirectResponse
    {
        try {
            $patient->delete();

            return redirect()->route('patients.index')
                ->with('success', 'Pasien berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pasien: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus pasien');
        }
    }
}
