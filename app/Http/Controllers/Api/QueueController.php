<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\QueueRequest;
use App\Http\Resources\QueueResource;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Queue::with(['patient', 'polyclinic', 'doctor', 'medicalRecord']);

            if ($request->filled('date')) {
                $query->where('queue_date', $request->date);
            }

            if ($request->filled('polyclinic_id')) {
                $query->where('polyclinic_id', $request->polyclinic_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $queues = $query->orderBy('id', 'asc')->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data' => QueueResource::collection($queues),
                'message' => 'Daftar antrean berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(QueueRequest $request): JsonResponse
    {
        try {
            $patient = \App\Models\Patient::findOrFail($request->patient_id);
            $polyclinic = Polyclinic::findOrFail($request->polyclinic_id);
            $doctor = $request->filled('doctor_id')
                ? \App\Models\Doctor::find($request->doctor_id)
                : null;

            $queue = $this->queueService->registerQueue(
                $patient,
                $polyclinic,
                $doctor,
                $request->service_type
            );

            if ($request->filled('notes')) {
                $queue->update(['notes' => $request->notes]);
            }

            return response()->json([
                'success' => true,
                'data' => new QueueResource($queue->load(['patient', 'polyclinic', 'doctor'])),
                'message' => 'Pendaftaran antrean berhasil',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal mendaftarkan antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Queue $queue): JsonResponse
    {
        try {
            $queue->load(['patient', 'polyclinic', 'doctor', 'medicalRecord']);

            return response()->json([
                'success' => true,
                'data' => new QueueResource($queue),
                'message' => 'Detail antrean berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function callNext(Request $request): JsonResponse
    {
        try {
            $request->validate(['polyclinic_code' => 'required|string|exists:polyclinics,code']);

            $queue = $this->queueService->callNext($request->polyclinic_code);

            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada antrean yang menunggu',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new QueueResource($queue->load(['patient', 'polyclinic', 'doctor'])),
                'message' => 'Pasien dipanggil',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memanggil antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memanggil antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markInProgress(Queue $queue): JsonResponse
    {
        try {
            $queue = $this->queueService->inProgress($queue);

            return response()->json([
                'success' => true,
                'data' => new QueueResource($queue->load(['patient', 'polyclinic', 'doctor'])),
                'message' => 'Status antrean diubah menjadi dalam pemeriksaan',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Gagal mengubah status antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markCompleted(Queue $queue): JsonResponse
    {
        try {
            $queue = $this->queueService->complete($queue);

            return response()->json([
                'success' => true,
                'data' => new QueueResource($queue->load(['patient', 'polyclinic', 'doctor'])),
                'message' => 'Antrean selesai',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Gagal menyelesaikan antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Queue $queue): JsonResponse
    {
        try {
            $queue = $this->queueService->cancel($queue);

            return response()->json([
                'success' => true,
                'data' => new QueueResource($queue->load(['patient', 'polyclinic', 'doctor'])),
                'message' => 'Antrean dibatalkan',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Gagal membatalkan antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function currentQueue(Request $request): JsonResponse
    {
        try {
            $request->validate(['polyclinic_code' => 'required|string|exists:polyclinics,code']);

            $polyclinic = Polyclinic::where('code', $request->polyclinic_code)->firstOrFail();

            $queues = $this->queueService->getQueueByPolyclinic($polyclinic, now()->toDateString());

            $current = $queues->whereIn('status', ['called', 'in_progress'])->first();
            $waiting = $queues->where('status', 'waiting')->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'current' => $current ? new QueueResource($current->load(['patient', 'doctor'])) : null,
                    'waiting' => QueueResource::collection($waiting),
                    'total_waiting' => $waiting->count(),
                ],
                'message' => 'Status antrean saat ini',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat status antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat status antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function displayData(Request $request): JsonResponse
    {
        try {
            $request->validate(['polyclinic_code' => 'required|string|exists:polyclinics,code']);

            $polyclinic = Polyclinic::where('code', $request->polyclinic_code)->firstOrFail();
            $queues = $this->queueService->getQueueByPolyclinic($polyclinic, now()->toDateString());

            $called = $queues->where('status', 'called')->first();
            $inProgress = $queues->where('status', 'in_progress')->first();
            $waiting = $queues->where('status', 'waiting')->take(5)->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'polyclinic' => $polyclinic->name,
                    'called' => $called ? [
                        'queue_number' => $called->queue_number,
                        'patient_name' => $called->patient?->name,
                    ] : null,
                    'in_progress' => $inProgress ? [
                        'queue_number' => $inProgress->queue_number,
                        'patient_name' => $inProgress->patient?->name,
                    ] : null,
                    'waiting_list' => $waiting->map(fn ($q) => [
                        'queue_number' => $q->queue_number,
                        'patient_name' => $q->patient?->name,
                    ]),
                ],
                'message' => 'Data tampilan antrean',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat data tampilan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data tampilan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        try {
            $date = $request->filled('date') ? $request->date : now()->toDateString();

            $stats = $this->queueService->getQueueStats($date);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistik antrean berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat statistik antrean: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat statistik antrean',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
