<?php

namespace App\Services;

use App\Models\Polyclinic;
use App\Models\Queue;

class VoiceCallService
{
    protected TtsProvider $ttsProvider;

    public function __construct(?TtsProvider $ttsProvider = null)
    {
        $this->ttsProvider = $ttsProvider ?? app(GoogleCloudTtsProvider::class);
    }

    public function announceQueueNumber(string $queueNumber, string $polyclinicName, string $roomNumber): string
    {
        $text = $this->generateSpeechText('queue_number', compact('queueNumber', 'polyclinicName', 'roomNumber'));
        $this->play($text);

        return $text;
    }

    public function announceCall(string $patientName, string $queueNumber, string $polyclinicName): string
    {
        $text = $this->generateSpeechText('call_patient', compact('patientName', 'queueNumber', 'polyclinicName'));
        $this->play($text);

        return $text;
    }

    public function generateSpeechText(string $type, array $params): string
    {
        return match ($type) {
            'queue_number' => sprintf(
                'Nomor antrean %s, silakan menuju poli %s ruangan %s.',
                $params['queueNumber'],
                $params['polyclinicName'],
                $params['roomNumber']
            ),
            'call_patient' => sprintf(
                'Pasien atas nama %s, nomor antrean %s. Silakan menuju poli %s.',
                $params['patientName'],
                $params['queueNumber'],
                $params['polyclinicName']
            ),
            'reminder' => sprintf(
                'Dipersilakan pasien nomor antrean %s untuk bersiap. Antrean Anda sudah dekat.',
                $params['queueNumber']
            ),
            'doctor_ready' => sprintf(
                'Dokter di poli %s sudah siap. Nomor antrean %s dipersilakan masuk.',
                $params['polyclinicName'],
                $params['queueNumber']
            ),
            'closing' => sprintf(
                'Terima kasih atas kunjungan Anda di poli %s. %s',
                $params['polyclinicName'],
                $params['additionalMessage'] ?? 'Semoga lekas sembuh.'
            ),
            default => $params['text'] ?? '',
        };
    }

    public function play(string $text, string $language = 'id'): void
    {
        $this->ttsProvider->synthesize($text, $language);
    }

    public function getQueueDisplayData(Polyclinic $polyclinic): array
    {
        $today = now()->toDateString();

        $currentQueue = Queue::with(['patient', 'doctor'])
            ->where('polyclinic_id', $polyclinic->id)
            ->where('queue_date', $today)
            ->where('status', 'in_progress')
            ->first();

        $calledQueues = Queue::with(['patient', 'doctor'])
            ->where('polyclinic_id', $polyclinic->id)
            ->where('queue_date', $today)
            ->where('status', 'called')
            ->orderBy('called_at', 'asc')
            ->get();

        $waitingQueues = Queue::with(['patient'])
            ->where('polyclinic_id', $polyclinic->id)
            ->where('queue_date', $today)
            ->where('status', 'waiting')
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get();

        $completedToday = Queue::where('polyclinic_id', $polyclinic->id)
            ->where('queue_date', $today)
            ->where('status', 'completed')
            ->count();

        $totalWaiting = $waitingQueues->count() + $calledQueues->count() + ($currentQueue ? 1 : 0);

        return [
            'polyclinic' => [
                'id' => $polyclinic->id,
                'code' => $polyclinic->code,
                'name' => $polyclinic->name,
                'location' => $polyclinic->location,
            ],
            'current' => $currentQueue ? [
                'queue_number' => $currentQueue->queue_number,
                'patient_name' => $currentQueue->patient->name,
                'doctor_name' => $currentQueue->doctor?->name,
            ] : null,
            'called' => $calledQueues->map(fn ($q) => [
                'queue_number' => $q->queue_number,
                'patient_name' => $q->patient->name,
                'called_at' => $q->called_at,
            ]),
            'waiting' => $waitingQueues->map(fn ($q) => [
                'queue_number' => $q->queue_number,
                'patient_name' => $q->patient->name,
            ]),
            'stats' => [
                'total_waiting' => $totalWaiting,
                'completed_today' => $completedToday,
            ],
            'updated_at' => now(),
        ];
    }
}
