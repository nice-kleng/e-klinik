<?php

namespace App\Services;

use App\Models\PharmacyQueue;
use App\Models\Prescription;
use App\Models\Registration;

class PharmacyQueueService
{
    protected const STATUS_WAITING = 'waiting';
    protected const STATUS_CALLED = 'called';
    protected const STATUS_IN_PROGRESS = 'in_progress';
    protected const STATUS_COMPLETED = 'completed';
    protected const STATUS_CANCELLED = 'cancelled';

    public function getNextSequence(string $date): int
    {
        $last = PharmacyQueue::whereDate('queue_date', $date)
            ->orderBy('queue_sequence', 'desc')
            ->first();

        return $last ? $last->queue_sequence + 1 : 1;
    }

    public function generateQueueNumber(string $date, int $sequence): string
    {
        return 'RX-' . $sequence;
    }

    public function createQueue(Registration $registration): ?PharmacyQueue
    {
        $existing = PharmacyQueue::where('registration_id', $registration->id)
            ->where('status', '!=', self::STATUS_CANCELLED)
            ->latest()
            ->first();

        if ($existing) {
            $registration->update(['service_status' => 'pharmacy']);

            return $existing;
        }

        $prescription = Prescription::with('medicalRecord')
            ->whereHas('medicalRecord', fn ($q) => $q->where('registration_id', $registration->id))
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$prescription) {
            return null;
        }

        $date = now()->toDateString();
        $sequence = $this->getNextSequence($date);
        $queueNumber = $this->generateQueueNumber($date, $sequence);

        $queue = PharmacyQueue::create([
            'registration_id' => $registration->id,
            'prescription_id' => $prescription->id,
            'queue_number' => $queueNumber,
            'queue_date' => $date,
            'queue_sequence' => $sequence,
            'status' => self::STATUS_WAITING,
            'created_by' => auth()->id(),
        ]);

        $registration->update(['service_status' => 'pharmacy']);

        return $queue;
    }

    public function callNext(): ?PharmacyQueue
    {
        $queue = PharmacyQueue::with('registration.patient')
            ->whereDate('queue_date', now()->toDateString())
            ->where('status', self::STATUS_WAITING)
            ->orderBy('queue_sequence', 'asc')
            ->first();

        if (!$queue) {
            return null;
        }

        $queue->update([
            'status' => self::STATUS_CALLED,
            'called_by' => auth()->id(),
            'called_at' => now(),
        ]);

        return $queue->fresh();
    }

    public function inProgress(PharmacyQueue $queue): PharmacyQueue
    {
        if ($queue->status !== self::STATUS_CALLED) {
            throw new \RuntimeException('Antrean farmasi harus dalam status dipanggil sebelum diproses.');
        }

        $queue->update(['status' => self::STATUS_IN_PROGRESS]);

        return $queue->fresh();
    }

    public function complete(PharmacyQueue $queue): PharmacyQueue
    {
        if (!in_array($queue->status, [self::STATUS_CALLED, self::STATUS_IN_PROGRESS])) {
            throw new \RuntimeException('Antrean farmasi harus dalam status dipanggil atau diproses sebelum selesai.');
        }

        $queue->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $registration = $queue->registration;
        if ($registration) {
            RegistrationFlowService::route($registration, 'pharmacy');
        }

        return $queue->fresh();
    }

    public function cancel(PharmacyQueue $queue): PharmacyQueue
    {
        if (in_array($queue->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            throw new \RuntimeException('Antrean farmasi sudah selesai atau dibatalkan.');
        }

        $queue->update(['status' => self::STATUS_CANCELLED]);

        return $queue->fresh();
    }

    public function getStats(string $date): array
    {
        $queues = PharmacyQueue::whereDate('queue_date', $date)->get();

        return [
            'waiting' => $queues->where('status', self::STATUS_WAITING)->count(),
            'called' => $queues->where('status', self::STATUS_CALLED)->count(),
            'in_progress' => $queues->where('status', self::STATUS_IN_PROGRESS)->count(),
            'completed' => $queues->where('status', self::STATUS_COMPLETED)->count(),
            'cancelled' => $queues->where('status', self::STATUS_CANCELLED)->count(),
        ];
    }
}
