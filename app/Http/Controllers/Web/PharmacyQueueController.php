<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PharmacyQueue;
use App\Services\PharmacyQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PharmacyQueueController extends Controller
{
    protected PharmacyQueueService $pharmacyQueueService;

    public function __construct(PharmacyQueueService $pharmacyQueueService)
    {
        $this->pharmacyQueueService = $pharmacyQueueService;
    }

    public function index(Request $request): View
    {
        $date = $request->filled('date') ? $request->date : now()->toDateString();

        $queues = PharmacyQueue::with(['registration.patient', 'registration.polyclinic', 'prescription', 'calledBy'])
            ->whereDate('queue_date', $date)
            ->orderBy('queue_sequence', 'asc')
            ->get();

        $stats = $this->pharmacyQueueService->getStats($date);
        $current = $queues->whereIn('status', ['called', 'in_progress'])->first();
        $waiting = $queues->where('status', 'waiting');
        $completed = $queues->whereIn('status', ['completed', 'cancelled']);

        return view('pharmacy-queues.index', compact('queues', 'stats', 'current', 'waiting', 'completed', 'date'));
    }

    public function callNext(): RedirectResponse
    {
        try {
            $queue = $this->pharmacyQueueService->callNext();

            if (!$queue) {
                return redirect()->route('pharmacy-queues.index')
                    ->with('error', 'Tidak ada pasien dalam antrian farmasi');
            }

            return redirect()->route('pharmacy-queues.index')
                ->with('success', 'Panggilan berikutnya: No. ' . $queue->queue_number . ' — ' . ($queue->registration?->patient?->name ?? 'Pasien'));
        } catch (\Exception $e) {
            Log::error('Gagal memanggil antrian farmasi: ' . $e->getMessage());

            return redirect()->route('pharmacy-queues.index')
                ->with('error', 'Gagal memanggil antrian farmasi');
        }
    }

    public function complete(PharmacyQueue $pharmacyQueue): RedirectResponse
    {
        try {
            $this->pharmacyQueueService->complete($pharmacyQueue);

            return redirect()->route('pharmacy-queues.index')
                ->with('success', 'Antrian farmasi No. ' . $pharmacyQueue->queue_number . ' selesai. Pasien dialihkan ke Kasir.');
        } catch (\RuntimeException $e) {
            return redirect()->route('pharmacy-queues.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Gagal menyelesaikan antrian farmasi: ' . $e->getMessage());

            return redirect()->route('pharmacy-queues.index')
                ->with('error', 'Gagal menyelesaikan antrian farmasi');
        }
    }

    public function cancel(PharmacyQueue $pharmacyQueue): RedirectResponse
    {
        try {
            $this->pharmacyQueueService->cancel($pharmacyQueue);

            return redirect()->route('pharmacy-queues.index')
                ->with('success', 'Antrian farmasi dibatalkan');
        } catch (\RuntimeException $e) {
            return redirect()->route('pharmacy-queues.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Gagal membatalkan antrian farmasi: ' . $e->getMessage());

            return redirect()->route('pharmacy-queues.index')
                ->with('error', 'Gagal membatalkan antrian farmasi');
        }
    }
}
