<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\Registration;
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

        return view('queues.show', compact('queue'));
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

            return redirect()->route('queues.index')
                ->with('success', 'Antrean ' . $nextQueue->queue_number . ' dipanggil');
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
                ->with('success', 'Status antrean diubah menjadi in progress');
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
                ->with('success', 'Antrean selesai');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyelesaikan antrean: ' . $e->getMessage());
        }
    }

    public function cancel(Queue $queue): RedirectResponse
    {
        try {
            $this->queueService->cancel($queue);

            return redirect()->route('queues.index')
                ->with('success', 'Antrean dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()
                .with('error', 'Gagal membatalkan antrean: ' . $e->getMessage());
        }
    }

    public function display(): View
    {
        $polyclinics = Polyclinic::where('is_active', true)->get()->map(function ($p) {
            $queues = Queue::with(['registration.patient', 'registration.doctor'])
                ->where('polyclinic_id', $p->id)
                ->whereDate('queue_date', now()->toDateString())
                ->whereIn('status', ['waiting', 'called', 'in_progress'])
                ->orderBy('queue_sequence', 'asc')
                ->get();

            $p->queues = $queues;
            return $p;
        });

        return view('queues.display', compact('polyclinics'));
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
