<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabResult;
use App\Models\LabTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LabResultController extends Controller
{
    public function index(Request $request): View
    {
        $query = LabResult::with([
            'patient', 'labTest.category', 'labRequestItem.labRequest',
            'examiner',
        ]);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->whereHas('patient', function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('no_rm', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('flag')) {
            $query->where('flag', $request->flag);
        }

        if ($request->filled('lab_test_id')) {
            $query->where('lab_test_id', $request->lab_test_id);
        }

        $results = $query->orderByDesc('examined_at')->paginate(15)->withQueryString();
        $labTests = LabTest::orderBy('name')->get();

        return view('lab-results.index', compact('results', 'labTests'));
    }

    public function input(LabRequest $labRequest): View
    {
        $labRequest->load([
            'patient', 'doctor.user', 'items.labTest', 'items.result',
        ]);

        return view('lab-results.input', compact('labRequest'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.lab_request_item_id' => 'required|exists:lab_request_items,id',
            'results.*.lab_test_id' => 'required|exists:lab_tests,id',
            'results.*.patient_id' => 'required|exists:patients,id',
            'results.*.result_value' => 'nullable|string|max:100',
            'results.*.result_text' => 'nullable|string',
            'results.*.ref_range_low' => 'nullable|string|max:50',
            'results.*.ref_range_high' => 'nullable|string|max:50',
            'results.*.ref_range_text' => 'nullable|string',
            'results.*.unit' => 'nullable|string|max:30',
            'results.*.flag' => 'required|in:normal,abnormal,critical,not_tested',
            'results.*.notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $labRequestItemIds = [];

                foreach ($validated['results'] as $item) {
                    $labRequestItemIds[] = $item['lab_request_item_id'];

                    LabResult::updateOrCreate(
                        ['lab_request_item_id' => $item['lab_request_item_id']],
                        [
                            'lab_test_id' => $item['lab_test_id'],
                            'patient_id' => $item['patient_id'],
                            'result_value' => $item['result_value'] ?? null,
                            'result_text' => $item['result_text'] ?? null,
                            'ref_range_low' => $item['ref_range_low'] ?? null,
                            'ref_range_high' => $item['ref_range_high'] ?? null,
                            'ref_range_text' => $item['ref_range_text'] ?? null,
                            'unit' => $item['unit'] ?? null,
                            'flag' => $item['flag'],
                            'notes' => $item['notes'] ?? null,
                            'examined_by' => Auth::id(),
                            'examined_at' => now(),
                            'created_by' => Auth::id(),
                        ]
                    );
                }

                LabRequestItem::whereIn('id', $labRequestItemIds)
                    ->update(['status' => 'completed']);

                $labRequestId = LabRequestItem::whereIn('id', $labRequestItemIds)
                    ->value('lab_request_id');

                $allCompleted = LabRequestItem::where('lab_request_id', $labRequestId)
                    ->where('status', '!=', 'completed')
                    ->doesntExist();

                if ($allCompleted) {
                    LabRequest::where('id', $labRequestId)
                        ->update(['status' => 'completed']);
                }
            });

            return redirect()->route('lab-results.index')
                ->with('success', 'Hasil laboratorium berhasil disimpan');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan hasil lab: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan hasil laboratorium');
        }
    }

    public function edit(LabResult $labResult): View
    {
        $labResult->load([
            'patient', 'labTest.category', 'labRequestItem.labRequest',
            'examiner',
        ]);

        return view('lab-results.edit', compact('labResult'));
    }

    public function update(Request $request, LabResult $labResult): RedirectResponse
    {
        $validated = $request->validate([
            'result_value' => 'nullable|string|max:100',
            'result_text' => 'nullable|string',
            'ref_range_low' => 'nullable|string|max:50',
            'ref_range_high' => 'nullable|string|max:50',
            'ref_range_text' => 'nullable|string',
            'unit' => 'nullable|string|max:30',
            'flag' => 'required|in:normal,abnormal,critical,not_tested',
            'notes' => 'nullable|string',
        ]);

        try {
            $labResult->update([
                ...$validated,
                'examined_by' => Auth::id(),
                'examined_at' => now(),
            ]);

            return redirect()->route('lab-results.index')
                ->with('success', 'Hasil laboratorium berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal update hasil lab: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui hasil laboratorium');
        }
    }
}
