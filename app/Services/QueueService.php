<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Doctor;
use App\Models\Queue;
use App\Models\User;
use App\Services\BpjsSepService;
use App\Services\BPJS\AntrolService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueService
{
    protected AntrolService $antrolService;
    protected BpjsSepService $bpjsSepService;

    protected const STATUS_WAITING = 'waiting';
    protected const STATUS_CALLED = 'called';
    protected const STATUS_IN_PROGRESS = 'in_progress';
    protected const STATUS_COMPLETED = 'completed';
    protected const STATUS_CANCELLED = 'cancelled';

    protected const AVERAGE_CONSULTATION_MINUTES = 15;

    public function __construct(
        ?AntrolService $antrolService = null,
        ?BpjsSepService $bpjsSepService = null
    ) {
        $this->antrolService = $antrolService ?? app(AntrolService::class);
        $this->bpjsSepService = $bpjsSepService ?? app(BpjsSepService::class);
    }

    public function generateQueueNumber(Polyclinic $polyclinic, string $date): string
    {
        $formattedDate = Carbon::parse($date)->format('Ymd');

        $lastQueue = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', $date)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastQueue && preg_match('/-(\d{3})$/', $lastQueue->queue_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%03d', $polyclinic->code, $formattedDate, $sequence);
    }

    public function registerQueue(
        Patient $patient,
        Polyclinic $polyclinic,
        ?Doctor $doctor,
        string $serviceType
    ): Queue {
        $date = now()->toDateString();
        $queueNumber = $this->generateQueueNumber($polyclinic, $date);

        $estimatedWait = $this->estimatedWaitTime($polyclinic);

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
            'doctor_id' => $doctor?->id,
            'queue_number' => $queueNumber,
            'queue_date' => $date,
            'status' => self::STATUS_WAITING,
            'estimated_wait_time' => $estimatedWait,
            'check_in_at' => now(),
            'service_type' => $serviceType,
            'created_by' => auth()->id(),
        ]);

        if ($patient->insurance_type === 'BPJS') {
            try {
                $this->syncToBpjs($queue);
            } catch (\Exception $e) {
                Log::warning('Failed to sync queue to BPJS Antrol', [
                    'queue_id' => $queue->id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $this->bpjsSepService->createFromQueue($queue);
            } catch (\Exception $e) {
                Log::warning('Failed to create SEP from queue', [
                    'queue_id' => $queue->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $queue;
    }

    public function callNext(string $polyclinicCode): ?Queue
    {
        $polyclinic = Polyclinic::where('code', $polyclinicCode)->firstOrFail();

        $queue = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', now()->toDateString())
            ->where('status', self::STATUS_WAITING)
            ->orderBy('id', 'asc')
            ->first();

        if (!$queue) {
            return null;
        }

        $queue->update([
            'status' => self::STATUS_CALLED,
            'called_at' => now(),
        ]);

        return $queue->fresh();
    }

    public function inProgress(Queue $queue): Queue
    {
        if ($queue->status !== self::STATUS_CALLED) {
            throw new \RuntimeException(
                'Queue must be in called status before marking as in progress.'
            );
        }

        $queue->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);

        return $queue->fresh();
    }

    public function complete(Queue $queue): Queue
    {
        if (!in_array($queue->status, [self::STATUS_CALLED, self::STATUS_IN_PROGRESS])) {
            throw new \RuntimeException(
                'Queue must be called or in progress before completing.'
            );
        }

        $queue->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        return $queue->fresh();
    }

    public function cancel(Queue $queue): Queue
    {
        if (in_array($queue->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            throw new \RuntimeException(
                'Queue already completed or cancelled.'
            );
        }

        $queue->update([
            'status' => self::STATUS_CANCELLED,
        ]);

        try {
            $this->cancelBpjsAntrean($queue);
        } catch (\Exception $e) {
            Log::warning('Failed to cancel BPJS antrean', [
                'queue_id' => $queue->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $queue->fresh();
    }

    public function getQueueByPolyclinic(Polyclinic $polyclinic, string $date): Collection
    {
        return Queue::with(['patient', 'doctor', 'medicalRecord'])
            ->where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', $date)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getQueueStats(string $date): array
    {
        $queues = Queue::whereDate('queue_date', $date)->get();

        return [
            'total' => $queues->count(),
            'waiting' => $queues->where('status', self::STATUS_WAITING)->count(),
            'called' => $queues->where('status', self::STATUS_CALLED)->count(),
            'in_progress' => $queues->where('status', self::STATUS_IN_PROGRESS)->count(),
            'completed' => $queues->where('status', self::STATUS_COMPLETED)->count(),
            'cancelled' => $queues->where('status', self::STATUS_CANCELLED)->count(),
            'by_polyclinic' => $queues->groupBy('polyclinic_id')
                ->map(fn ($group) => [
                    'polyclinic_id' => $group->first()->polyclinic_id,
                    'total' => $group->count(),
                    'waiting' => $group->where('status', self::STATUS_WAITING)->count(),
                    'completed' => $group->where('status', self::STATUS_COMPLETED)->count(),
                ])
                ->values()
                ->toArray(),
        ];
    }

    public function estimatedWaitTime(Polyclinic $polyclinic): int
    {
        $todayDate = now()->toDateString();

        $waitingCount = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', $todayDate)
            ->whereIn('status', [self::STATUS_WAITING, self::STATUS_CALLED])
            ->count();

        $recentCompleted = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', $todayDate)
            ->where('status', self::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->whereNotNull('called_at')
            ->get();

        $avgMinutes = self::AVERAGE_CONSULTATION_MINUTES;

        if ($recentCompleted->count() >= 3) {
            $totalMinutes = $recentCompleted->sum(function ($q) {
                return $q->called_at->diffInMinutes($q->completed_at);
            });
            $avgMinutes = max(5, (int) round($totalMinutes / $recentCompleted->count()));
        }

        return $waitingCount * $avgMinutes;
    }

    public function syncToBpjs(Queue $queue): ?array
    {
        if ($queue->patient->insurance_type !== 'BPJS') {
            return null;
        }

        $bpjsPatient = $queue->patient->bpjsPatient;
        if (!$bpjsPatient || !$bpjsPatient->no_kartu) {
            Log::warning('BPJS patient has no card number for queue sync', [
                'queue_id' => $queue->id,
                'patient_id' => $queue->patient_id,
            ]);
            return null;
        }

        $polyclinicCode = $queue->polyclinic->code;

        $data = [
            'noKartu' => $bpjsPatient->no_kartu,
            'nik' => $queue->patient->nik,
            'noRm' => $queue->patient->no_rm,
            'kodePoli' => $polyclinicCode,
            'kodeDokter' => $queue->doctor?->code ?? '',
            'noAntrean' => $queue->queue_number,
            'tanggal' => $queue->queue_date->format('Y-m-d'),
            'jamPendaftaran' => $queue->check_in_at?->format('H:i:s') ?? now()->format('H:i:s'),
        ];

        $response = $this->antrolService->addAntrean($data);

        if ($response && isset($response['noAntrean'])) {
            $queue->update([
                'bpjs_antrian_id' => $response['noAntrean'] ?? null,
            ]);
        }

        return $response;
    }

    protected function cancelBpjsAntrean(Queue $queue): ?array
    {
        if (!$queue->bpjs_antrian_id) {
            return null;
        }

        try {
            return $this->antrolService->deleteAntrean($queue->bpjs_antrian_id);
        } catch (\Exception $e) {
            Log::error('Failed to delete BPJS antrean', [
                'queue_id' => $queue->id,
                'bpjs_antrian_id' => $queue->bpjs_antrian_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
