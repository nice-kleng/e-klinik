<?php

namespace App\Services;

use App\Models\LabRequest;
use App\Models\Prescription;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;

class RegistrationFlowService
{
    public static function route(Registration $registration, string $from): string
    {
        $hasLab = LabRequest::where('status', '!=', 'cancelled')
            ->whereHas('medicalRecord', fn ($q) => $q->where('registration_id', $registration->id))
            ->exists();

        $hasActivePrescription = Prescription::where('status', 'active')
            ->whereHas('medicalRecord', fn ($q) => $q->where('registration_id', $registration->id))
            ->exists();

        if ($from === 'in_consultation') {
            if ($hasLab) {
                self::createLabQueue($registration);
                return 'lab';
            }

            if ($hasActivePrescription) {
                self::createPharmacyQueue($registration);
                return 'pharmacy';
            }

            $registration->update(['service_status' => 'cashier']);
            return 'cashier';
        }

        if ($from === 'lab') {
            if ($hasActivePrescription) {
                $alreadyQueued = \App\Models\PharmacyQueue::where('registration_id', $registration->id)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if (!$alreadyQueued) {
                    self::createPharmacyQueue($registration);
                } else {
                    $registration->update(['service_status' => 'pharmacy']);
                }

                return 'pharmacy';
            }

            $registration->update(['service_status' => 'cashier']);
            return 'cashier';
        }

        if ($from === 'pharmacy') {
            $registration->update(['service_status' => 'cashier']);
            return 'cashier';
        }

        throw new \InvalidArgumentException("Unknown flow origin: {$from}");
    }

    protected static function createLabQueue(Registration $registration): void
    {
        try {
            app(LabQueueService::class)->createQueue($registration);
        } catch (\Exception $e) {
            Log::error('Gagal membuat antrian lab: ' . $e->getMessage());
        }
    }

    protected static function createPharmacyQueue(Registration $registration): void
    {
        try {
            app(PharmacyQueueService::class)->createQueue($registration);
        } catch (\Exception $e) {
            Log::error('Gagal membuat antrian farmasi: ' . $e->getMessage());
        }
    }
}
