<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LabRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = LabRequest::with(['patient', 'doctor.user', 'medicalRecord', 'items.labTest']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->whereHas('patient', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('no_rm', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('lab-requests.index', compact('requests'));
    }

    public function create(): View
    {
        $patients = Patient::orderBy('name')->get();
        $medicalRecords = MedicalRecord::with('patient')->orderByDesc('created_at')->get();
        $labTests = LabTest::with('category')->where('is_active', true)->orderBy('name')->get();

        return view('lab-requests.create', compact('patients', 'medicalRecords', 'labTests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medical_record_id' => 'required|exists:medical_records,id',
            'patient_id' => 'required|exists:patients,id',
            'notes' => 'nullable|string',
            'lab_test_ids' => 'required|array|min:1',
            'lab_test_ids.*' => 'exists:lab_tests,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $labRequest = LabRequest::create([
                    'medical_record_id' => $validated['medical_record_id'],
                    'patient_id' => $validated['patient_id'],
                    'doctor_id' => Auth::user()->doctor?->id,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'requested',
                    'created_by' => Auth::id(),
                ]);

                foreach ($validated['lab_test_ids'] as $testId) {
                    LabRequestItem::create([
                        'lab_request_id' => $labRequest->id,
                        'lab_test_id' => $testId,
                        'status' => 'pending',
                        'created_by' => Auth::id(),
                    ]);
                }
            });

            return redirect()->route('lab-requests.index')
                ->with('success', 'Permintaan laboratorium berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Gagal membuat permintaan lab: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat permintaan laboratorium');
        }
    }

    public function show(LabRequest $labRequest): View
    {
        $labRequest->load([
            'patient', 'doctor.user', 'medicalRecord',
            'items.labTest.category', 'items.result',
            'creator',
        ]);

        return view('lab-requests.show', compact('labRequest'));
    }

    public function updateStatus(Request $request, LabRequest $labRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:requested,sampled,processing,completed,cancelled',
        ]);

        try {
            $labRequest->update(['status' => $validated['status']]);

            $message = match ($validated['status']) {
                'sampled' => 'Status diubah: Sampel sudah diambil',
                'processing' => 'Status diubah: Sedang diproses',
                'completed' => 'Status diubah: Selesai',
                'cancelled' => 'Permintaan dibatalkan',
                default => 'Status berhasil diperbarui',
            };

            return redirect()->route('lab-requests.show', $labRequest)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Gagal update status lab request: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal memperbarui status');
        }
    }
}
