<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class QueueController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function index(Request $request): View
    {
        $date = $request->get('date', now()->toDateString());
        $polyclinicId = $request->get('polyclinic_id');
        $status = $request->get('status');

        $query = Queue::with(['patient', 'polyclinic', 'doctor'])
            ->where('queue_date', $date);

        if ($polyclinicId) {
            $query->where('polyclinic_id', $polyclinicId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $queues = $query->orderBy('id', 'asc')->paginate(20)->withQueryString();

        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();

        return view('queues.index', compact('queues', 'polyclinics', 'date', 'polyclinicId', 'status'));
    }

    public function create(): View
    {
        $patients = Patient::orderBy('name')->get();
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('polyclinic')->where('is_active', true)->orderBy('name')->get();

        return view('queues.create', compact('patients', 'polyclinics', 'doctors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'service_type' => 'required|string|in:umum,BPJS,Asuransi',
            'notes' => 'nullable|string',
        ]);

        try {
            $patient = Patient::findOrFail($validated['patient_id']);
            $polyclinic = Polyclinic::findOrFail($validated['polyclinic_id']);
            $doctor = $validated['doctor_id'] ? Doctor::find($validated['doctor_id']) : null;

            $this->queueService->registerQueue(
                $patient,
                $polyclinic,
                $doctor,
                $validated['service_type']
            );

            return redirect()->route('queues.index')
                ->with('success', 'Antrian berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan antrian: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan antrian: ' . $e->getMessage());
        }
    }

    public function show(Queue $queue): View
    {
        $queue->load(['patient', 'polyclinic', 'doctor', 'creator', 'medicalRecord']);

        return view('queues.show', compact('queue'));
    }

    public function call(Queue $queue): RedirectResponse
    {
        try {
            $this->queueService->callNext($queue->polyclinic->code);

            return redirect()->route('queues.index')
                ->with('success', 'Antrian dipanggil');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memanggil antrian: ' . $e->getMessage());
        }
    }

    public function inProgress(Queue $queue): RedirectResponse
    {
        try {
            $this->queueService->inProgress($queue);

            return redirect()->route('queues.index')
                ->with('success', 'Status antrian diubah menjadi in progress');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function complete(Queue $queue): RedirectResponse
    {
        try {
            $this->queueService->complete($queue);

            return redirect()->route('queues.index')
                ->with('success', 'Antrian selesai');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyelesaikan antrian: ' . $e->getMessage());
        }
    }

    public function cancel(Queue $queue): RedirectResponse
    {
        try {
            $this->queueService->cancel($queue);

            return redirect()->route('queues.index')
                ->with('success', 'Antrian dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membatalkan antrian: ' . $e->getMessage());
        }
    }

    public function display(): View
    {
        $polyclinics = Polyclinic::where('is_active', true)->with(['queues' => function ($query) {
            $query->where('queue_date', now()->toDateString())
                ->whereIn('status', ['waiting', 'called', 'in_progress'])
                ->orderBy('id', 'asc')
                ->with(['patient', 'doctor']);
        }])->get();

        return view('queues.display', compact('polyclinics'));
    }
}
