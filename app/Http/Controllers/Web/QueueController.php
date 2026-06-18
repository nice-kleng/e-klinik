<?php

namespace App\Http\Controllers\Web;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\Registration;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $query = Queue::with(['registration.patient', 'registration.doctor', 'polyclinic'])
            ->where('queue_date', $date);

        if ($polyclinicId) {
            $query->where('polyclinic_id', $polyclinicId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $queues = $query->orderBy('queue_sequence', 'asc')->paginate(20)->withQueryString();

        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();

        return view('queues.index', compact('queues', 'polyclinics', 'date', 'polyclinicId', 'status'));
    }

    public function show(Queue $queue): View
    {
        $queue->load([
            'registration.patient',
            'registration.doctor',
            'registration.polyclinic',
            'polyclinic',
            'creator',
            'medicalRecord',
            'queueCalls',
        ]);

        $previousRecords = collect();
        if ($queue->registration && $queue->registration->patient) {
            $previousRecords = MedicalRecord::with(['polyclinic', 'doctor'])
                ->where('patient_id', $queue->registration->patient_id)
                ->where('id', '!=', $queue->medicalRecord?->id)
                ->orderBy('visit_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('queues.show', compact('queue', 'previousRecords'));
    }

    public function call(Queue $queue): RedirectResponse
    {
        try {
            $polyclinic = $queue->polyclinic;
            $nextQueue = $this->queueService->callNext($polyclinic);

            if (!$nextQueue) {
                return redirect()->route('queues.index')
                    ->with('info', 'Tidak ada antrean yang menunggu');
            }

            QueueUpdated::dispatch(
                queueId: $nextQueue->id,
                polyclinicId: $polyclinic->id,
                status: 'called',
                action: 'called',
                queueNumber: $nextQueue->queue_number,
                patientName: $nextQueue->registration?->patient?->name,
                polyclinicName: $polyclinic->name,
            );

            return redirect()->route('queues.index')
                ->with('success', 'Antrean ' . $nextQueue->queue_number . ' dipanggil');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memanggil antrian: ' . $e->getMessage());
        }
    }

    public function callAjax(Queue $queue): JsonResponse
    {
        try {
            $polyclinic = $queue->polyclinic;
            $nextQueue = $this->queueService->callAndProgress($polyclinic);

            if (!$nextQueue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada antrean yang menunggu',
                ]);
            }

            QueueUpdated::dispatch(
                queueId: $nextQueue->id,
                polyclinicId: $polyclinic->id,
                status: 'in_progress',
                action: 'called',
                queueNumber: $nextQueue->queue_number,
                patientName: $nextQueue->registration?->patient?->name,
                polyclinicName: $polyclinic->name,
            );

            return response()->json([
                'success' => true,
                'message' => 'Antrean ' . $nextQueue->queue_number . ' dipanggil',
                'data' => [
                    'queue' => [
                        'id' => $nextQueue->id,
                        'queue_number' => $nextQueue->queue_number,
                        'status' => $nextQueue->status,
                        'patient_name' => $nextQueue->registration?->patient?->name ?? '-',
                        'polyclinic_name' => $polyclinic->name,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memanggil antrian: ' . $e->getMessage(),
            ]);
        }
    }

    public function queueData(Queue $queue): JsonResponse
    {
        $queue->load(['registration.patient', 'polyclinic']);

        return response()->json([
            'id' => $queue->id,
            'queue_number' => $queue->queue_number,
            'status' => $queue->status,
            'patient_name' => $queue->registration?->patient?->name ?? '-',
            'polyclinic_name' => $queue->polyclinic?->name ?? '-',
            'source' => $queue->source,
            'status_label' => match($queue->status) {
                'waiting' => 'Menunggu',
                'called' => 'Dipanggil',
                'in_progress' => 'Diproses',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                default => $queue->status,
            },
        ]);
    }

    public function inProgress(Request $request, Queue $queue): RedirectResponse|JsonResponse
    {
        try {
            $this->queueService->inProgress($queue);

            QueueUpdated::dispatch(
                queueId: $queue->id,
                polyclinicId: $queue->polyclinic_id,
                status: 'in_progress',
                action: 'in_progress',
                queueNumber: $queue->queue_number,
                patientName: $queue->registration?->patient?->name,
                polyclinicName: $queue->polyclinic?->name,
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Status antrean diubah menjadi in progress']);
            }

            return redirect()->route('queues.index')
                ->with('success', 'Status antrean diubah menjadi in progress');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal mengubah status: ' . $e->getMessage()]);
            }
            return redirect()->back()
                ->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function complete(Request $request, Queue $queue): RedirectResponse|JsonResponse
    {
        try {
            $this->queueService->complete($queue);

            QueueUpdated::dispatch(
                queueId: $queue->id,
                polyclinicId: $queue->polyclinic_id,
                status: 'completed',
                action: 'completed',
                queueNumber: $queue->queue_number,
                patientName: $queue->registration?->patient?->name,
                polyclinicName: $queue->polyclinic?->name,
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Antrean selesai']);
            }

            return redirect()->route('queues.index')
                ->with('success', 'Antrean selesai');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menyelesaikan antrean: ' . $e->getMessage()]);
            }
            return redirect()->back()
                ->with('error', 'Gagal menyelesaikan antrean: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Queue $queue): RedirectResponse|JsonResponse
    {
        try {
            $this->queueService->cancel($queue);

            QueueUpdated::dispatch(
                queueId: $queue->id,
                polyclinicId: $queue->polyclinic_id,
                status: 'cancelled',
                action: 'cancelled',
                queueNumber: $queue->queue_number,
                patientName: $queue->registration?->patient?->name,
                polyclinicName: $queue->polyclinic?->name,
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Antrean dibatalkan']);
            }

            return redirect()->route('queues.index')
                ->with('success', 'Antrean dibatalkan');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal membatalkan antrean: ' . $e->getMessage()]);
            }
            return redirect()->back()
                ->with('error', 'Gagal membatalkan antrean: ' . $e->getMessage());
        }
    }

    public function displayJson(): JsonResponse
    {
        $polyclinics = Polyclinic::where('is_active', true)->get()->map(function ($p) {
            $queues = Queue::with(['registration.patient', 'registration.doctor'])
                ->where('polyclinic_id', $p->id)
                ->whereDate('queue_date', now()->toDateString())
                ->whereIn('status', ['waiting', 'called', 'in_progress'])
                ->orderBy('queue_sequence', 'asc')
                ->get()
                ->map(fn($q) => [
                    'id' => $q->id,
                    'queue_number' => $q->queue_number,
                    'status' => $q->status,
                    'patient_name' => $q->registration?->patient?->name ?? '-',
                ]);

            return [
                'id' => $p->id,
                'name' => $p->name,
                'queues' => $queues,
            ];
        });

        return response()->json($polyclinics);
    }

    public function displayJsonPoly(Polyclinic $polyclinic): JsonResponse
    {
        $queues = Queue::with(['registration.patient', 'registration.doctor'])
            ->where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', now()->toDateString())
            ->whereIn('status', ['waiting', 'called', 'in_progress'])
            ->orderBy('queue_sequence', 'asc')
            ->get();

        $current = $queues->firstWhere('status', 'in_progress');
        $called = $queues->firstWhere('status', 'called');
        $waiting = $queues->where('status', 'waiting')->values();

        return response()->json([
            'polyclinic' => [
                'id' => $polyclinic->id,
                'name' => $polyclinic->name,
                'code' => $polyclinic->code,
            ],
            'doctor_name' => $current?->registration?->doctor?->name
                ?? $called?->registration?->doctor?->name
                ?? $waiting->first()?->registration?->doctor?->name
                ?? '-',
            'current' => $current ? [
                'id' => $current->id,
                'queue_number' => $current->queue_number,
                'patient_name' => $current->registration?->patient?->name ?? '-',
            ] : null,
            'called' => $called ? [
                'id' => $called->id,
                'queue_number' => $called->queue_number,
                'patient_name' => $called->registration?->patient?->name ?? '-',
            ] : null,
            'waiting' => $waiting->map(fn($q) => [
                'id' => $q->id,
                'queue_number' => $q->queue_number,
                'patient_name' => $q->registration?->patient?->name ?? '-',
                'doctor_name' => $q->registration?->doctor?->name ?? '-',
            ]),
            'waiting_count' => $waiting->count(),
        ]);
    }

    public function display(): View
    {
        $polyclinics = Polyclinic::where('is_active', true)->get()->map(function ($p) {
            $waiting = Queue::where('polyclinic_id', $p->id)
                ->whereDate('queue_date', now()->toDateString())
                ->where('status', 'waiting')
                ->count();

            $p->waiting_count = $waiting;
            return $p;
        });

        return view('queues.display', compact('polyclinics'));
    }

    public function displayTv(Polyclinic $polyclinic): View
    {
        $queues = Queue::with(['registration.patient', 'registration.doctor'])
            ->where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', now()->toDateString())
            ->whereIn('status', ['waiting', 'called', 'in_progress'])
            ->orderBy('queue_sequence', 'asc')
            ->get();

        $current = $queues->firstWhere('status', 'in_progress');
        $called = $queues->firstWhere('status', 'called');
        $waiting = $queues->where('status', 'waiting');

        return view('queues.display-tv', compact('polyclinic', 'current', 'called', 'waiting'));
    }

    public function history(Registration $registration): View
    {
        $registration->load([
            'patient',
            'polyclinic',
            'doctor',
            'queue.queueCalls',
            'queue.queueMilestones',
            'medicalRecords',
        ]);

        return view('queues.history', compact('registration'));
    }
}
