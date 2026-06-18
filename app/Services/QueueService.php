<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Events\QueueUpdated;
use App\Models\Queue;
use App\Models\QueueCall;
use App\Models\QueueMilestone;
use App\Models\Registration;
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

    public function __construct(
        ?AntrolService $antrolService = null,
        ?BpjsSepService $bpjsSepService = null
    ) {
        $this->antrolService = $antrolService ?? app(AntrolService::class);
        $this->bpjsSepService = $bpjsSepService ?? app(BpjsSepService::class);
    }

    public function getNextSequence(Polyclinic $polyclinic, string $date): int
    {
        $last = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', $date)
            ->orderBy('queue_sequence', 'desc')
            ->first();

        return $last ? $last->queue_sequence + 1 : 1;
    }

    public function generateQueueNumber(Polyclinic $polyclinic, string $date, int $sequence): string
    {
        return sprintf('%s-%03d', $polyclinic->code, $sequence);
    }

    public function registerQueue(
        Patient $patient,
        Polyclinic $polyclinic,
        ?Doctor $doctor,
        string $source,
        ?string $bpjsAntrianId = null,
        ?string $noSep = null,
        ?string $visitType = null,
    ): array {
        $date = now()->toDateString();
        $sequence = $this->getNextSequence($polyclinic, $date);
        $queueNumber = $this->generateQueueNumber($polyclinic, $date, $sequence);

        $age = Registration::calculateAge(
            Carbon::parse($patient->birth_date),
            now()
        );

        $prevCount = Registration::where('patient_id', $patient->id)->count();
        $visitType = $visitType ?? ($prevCount === 0 ? 'Baru' : 'Lama');
        $visitSequence = $prevCount + 1;

        $registration = Registration::create([
            'registration_number' => Registration::generateNumber(),
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
            'doctor_id' => $doctor?->id,
            'registration_date' => $date,
            'source' => $source,
            'service_status' => 'registered',
            'visit_type' => $visitType,
            'visit_sequence' => $visitSequence,
            'bpjs_antrian_id' => $bpjsAntrianId,
            'no_sep' => $noSep,
            'age_text' => $age['text'],
            'age_years' => $age['years'],
            'age_months' => $age['months'],
            'age_days' => $age['days'],
            'created_by' => auth()->id(),
        ]);

        $queue = Queue::create([
            'registration_id' => $registration->id,
            'polyclinic_id' => $polyclinic->id,
            'queue_sequence' => $sequence,
            'queue_number' => $queueNumber,
            'queue_date' => $date,
            'source' => $source,
            'status' => self::STATUS_WAITING,
            'check_in_at' => now(),
            'confirmed_at' => $source === 'mjkn' ? now() : null,
            'created_by' => auth()->id(),
        ]);

        QueueUpdated::dispatch(
            queueId: $queue->id,
            polyclinicId: $polyclinic->id,
            status: 'waiting',
            action: 'created',
            queueNumber: $queue->queue_number,
            patientName: $patient->name,
            polyclinicName: $polyclinic->name,
        );

        if ($patient->insurance_type === 'BPJS') {
            try {
                $this->syncToBpjs($queue, $registration);
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

        return ['registration' => $registration, 'queue' => $queue];
    }

    public function callNext(Polyclinic $polyclinic): ?Queue
    {
        $queue = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', now()->toDateString())
            ->where('status', self::STATUS_WAITING)
            ->orderBy('queue_sequence', 'asc')
            ->first();

        if (!$queue) {
            return null;
        }

        $lastCall = QueueCall::where('queue_id', $queue->id)
            ->max('call_sequence');

        QueueCall::create([
            'queue_id' => $queue->id,
            'polyclinic_id' => $polyclinic->id,
            'called_by' => auth()->id(),
            'call_sequence' => ($lastCall ?? 0) + 1,
            'called_at' => now(),
        ]);

        $queue->update([
            'status' => self::STATUS_CALLED,
        ]);

        return $queue->fresh();
    }

    public function callAndProgress(Polyclinic $polyclinic): ?Queue
    {
        $queue = Queue::where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', now()->toDateString())
            ->where('status', self::STATUS_WAITING)
            ->orderBy('queue_sequence', 'asc')
            ->first();

        if (!$queue) {
            return null;
        }

        $lastCall = QueueCall::where('queue_id', $queue->id)
            ->max('call_sequence');

        QueueCall::create([
            'queue_id' => $queue->id,
            'polyclinic_id' => $polyclinic->id,
            'called_by' => auth()->id(),
            'call_sequence' => ($lastCall ?? 0) + 1,
            'called_at' => now(),
        ]);

        $queue->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);

        $queue->registration?->update(['service_status' => 'in_consultation']);

        return $queue->fresh();
    }

    public function inProgress(Queue $queue): Queue
    {
        if ($queue->status !== self::STATUS_CALLED) {
            throw new \RuntimeException(
                'Antrean harus dalam status dipanggil sebelum masuk pemeriksaan.'
            );
        }

        $queue->update(['status' => self::STATUS_IN_PROGRESS]);

        $queue->registration?->update(['service_status' => 'in_consultation']);

        return $queue->fresh();
    }

    public function complete(Queue $queue): Queue
    {
        if (!in_array($queue->status, [self::STATUS_CALLED, self::STATUS_IN_PROGRESS])) {
            throw new \RuntimeException(
                'Antrean harus dalam status dipanggil atau diperiksa sebelum selesai.'
            );
        }

        $queue->update(['status' => self::STATUS_COMPLETED]);

        $queue->registration?->update(['service_status' => 'completed']);

        return $queue->fresh();
    }

    public function cancel(Queue $queue): Queue
    {
        if (in_array($queue->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            throw new \RuntimeException('Antrean sudah selesai atau dibatalkan.');
        }

        $queue->update(['status' => self::STATUS_CANCELLED]);

        $queue->registration?->update(['service_status' => 'cancelled']);

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
        return Queue::with([
                'registration.patient',
                'registration.doctor',
                'registration.polyclinic',
                'medicalRecord',
            ])
            ->where('polyclinic_id', $polyclinic->id)
            ->whereDate('queue_date', $date)
            ->orderBy('queue_sequence', 'asc')
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
                    'called' => $group->where('status', self::STATUS_CALLED)->count(),
                    'in_progress' => $group->where('status', self::STATUS_IN_PROGRESS)->count(),
                    'completed' => $group->where('status', self::STATUS_COMPLETED)->count(),
                ])
                ->values()
                ->toArray(),
        ];
    }

    public function updateServiceStatus(Queue $queue, string $status): void
    {
        $registration = $queue->registration;
        if ($registration) {
            $registration->update(['service_status' => $status]);
        }
    }

    public function syncToBpjs(Queue $queue, Registration $registration): ?array
    {
        $patient = $registration->patient;
        if (!$patient || $patient->insurance_type !== 'BPJS') {
            return null;
        }

        $bpjsPatient = $patient->bpjsPatient;
        if (!$bpjsPatient || !$bpjsPatient->no_kartu) {
            Log::warning('BPJS patient has no card number for queue sync', [
                'queue_id' => $queue->id,
                'registration_id' => $registration->id,
            ]);
            return null;
        }

        $data = [
            'noKartu' => $bpjsPatient->no_kartu,
            'nik' => $patient->nik,
            'noRm' => $patient->no_rm,
            'kodePoli' => $polyclinicCode = $registration->polyclinic?->code ?? '',
            'kodeDokter' => $registration->doctor?->code ?? '',
            'noAntrean' => $queue->queue_number,
            'tanggal' => $registration->registration_date->format('Y-m-d'),
            'jamPendaftaran' => $queue->check_in_at?->format('H:i:s') ?? now()->format('H:i:s'),
        ];

        $response = $this->antrolService->addAntrean($data);

        if ($response && isset($response['noAntrean'])) {
            $registration->update([
                'bpjs_antrian_id' => $response['noAntrean'] ?? null,
            ]);
        }

        return $response;
    }

    protected function cancelBpjsAntrean(Queue $queue): ?array
    {
        $registration = $queue->registration;
        if (!$registration || !$registration->bpjs_antrian_id) {
            return null;
        }

        try {
            return $this->antrolService->deleteAntrean($registration->bpjs_antrian_id);
        } catch (\Exception $e) {
            Log::error('Failed to delete BPJS antrean', [
                'queue_id' => $queue->id,
                'bpjs_antrian_id' => $registration->bpjs_antrian_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function callBack(Queue $queue): Queue
    {
        $polyclinic = $queue->polyclinic;

        $lastCall = QueueCall::where('queue_id', $queue->id)
            ->max('call_sequence');

        QueueCall::create([
            'queue_id' => $queue->id,
            'polyclinic_id' => $polyclinic->id,
            'called_by' => auth()->id(),
            'call_sequence' => ($lastCall ?? 0) + 1,
            'called_at' => now(),
        ]);

        $queue->update(['status' => self::STATUS_CALLED]);

        return $queue->fresh();
    }
}
