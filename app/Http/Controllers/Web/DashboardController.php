<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function index(Request $request): View
    {
        $date = $request->get('date', now()->toDateString());

        try {
            $totalPatientsToday = Patient::whereDate('created_at', $date)->count();

            $queueStats = $this->queueService->getQueueStats($date);

            $queueByPolyclinic = collect($queueStats['by_polyclinic'] ?? [])
                ->map(function ($item) {
                    $polyclinic = \App\Models\Polyclinic::find($item['polyclinic_id']);
                    $item['polyclinic_name'] = $polyclinic?->name ?? 'Unknown';
                    return $item;
                });
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            $totalPatientsToday = 0;
            $queueStats = [
                'total' => 0, 'waiting' => 0, 'called' => 0,
                'in_progress' => 0, 'completed' => 0, 'cancelled' => 0,
                'by_polyclinic' => [],
            ];
            $queueByPolyclinic = collect();
        }

        return view('dashboard', compact('totalPatientsToday', 'queueStats', 'queueByPolyclinic', 'date'));
    }
}
