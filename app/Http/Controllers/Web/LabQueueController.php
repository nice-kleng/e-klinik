<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LabQueue;
use App\Services\LabQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LabQueueController extends Controller
{
    protected LabQueueService $labQueueService;

    public function __construct(LabQueueService $labQueueService)
    {
        $this->labQueueService = $labQueueService;
    }

    public function index(Request $request): View
    {
        $date = $request->filled('date') ? $request->date : now()->toDateString();

        $queues = LabQueue::with(['registration.patient', 'registration.polyclinic', 'labRequest', 'calledBy'])
            ->whereDate('queue_date', $date)
            ->orderBy('queue_sequence', 'asc')
            ->get();

        $stats = $this->labQueueService->getStats($date);
        $current = $queues->whereIn('status', ['called', 'in_progress'])->first();
        $waiting = $queues->where('status', 'waiting');
        $completed = $queues->whereIn('status', ['completed', 'cancelled']);

        return view('lab-queues.index', compact('queues', 'stats', 'current', 'waiting', 'completed', 'date'));
    }

    public function callNext(): RedirectResponse
    {
        try {
            $queue = $this->labQueueService->callNext();

            if (!$queue) {
                return redirect()->route('lab-queues.index')
                    ->with('error', 'Tidak ada pasien dalam antrian laboratorium');
            }

            return redirect()->route('lab-queues.index')
                ->with('success', 'Panggilan berikutnya: No. ' . $queue->queue_number . ' — ' . ($queue->registration?->patient?->name ?? 'Pasien'));
        } catch (\Exception $e) {
            Log::error('Gagal memanggil antrian lab: ' . $e->getMessage());

            return redirect()->route('lab-queues.index')
                ->with('error', 'Gagal memanggil antrian laboratorium');
        }
    }

    public function complete(LabQueue $labQueue): RedirectResponse
    {
        try {
            $this->labQueueService->complete($labQueue);

            $message = 'Antrian lab No. ' . $labQueue->queue_number . ' selesai. ';

            $registration = $labQueue->registration;
            $next = $registration?->service_status;

            if ($next === 'pharmacy') {
                $message .= 'Pasien dialihkan ke Antrian Farmasi.';
            } elseif ($next === 'cashier') {
                $message .= 'Pasien dialihkan ke Kasir.';
            }

            return redirect()->route('lab-queues.index')
                ->with('success', $message);
        } catch (\RuntimeException $e) {
            return redirect()->route('lab-queues.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Gagal menyelesaikan antrian lab: ' . $e->getMessage());

            return redirect()->route('lab-queues.index')
                ->with('error', 'Gagal menyelesaikan antrian laboratorium');
        }
    }

    public function cancel(LabQueue $labQueue): RedirectResponse
    {
        try {
            $this->labQueueService->cancel($labQueue);

            return redirect()->route('lab-queues.index')
                ->with('success', 'Antrian lab dibatalkan');
        } catch (\RuntimeException $e) {
            return redirect()->route('lab-queues.index')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Gagal membatalkan antrian lab: ' . $e->getMessage());

            return redirect()->route('lab-queues.index')
                ->with('error', 'Gagal membatalkan antrian laboratorium');
        }
    }
}
