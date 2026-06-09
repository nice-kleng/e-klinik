<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use App\Models\LabTestCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LabTestController extends Controller
{
    public function index(Request $request): View
    {
        $query = LabTest::with('category');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $tests = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = LabTestCategory::orderBy('name')->get();

        return view('lab-tests.index', compact('tests', 'categories'));
    }

    public function create(): View
    {
        $categories = LabTestCategory::where('is_active', true)->orderBy('name')->get();

        return view('lab-tests.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:lab_test_categories,id',
            'code' => 'required|string|max:20|unique:lab_tests,code',
            'name' => 'required|string|max:255',
            'specimen_type' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:30',
            'gender' => 'nullable|in:L,P',
            'age_min' => 'nullable|integer|min:0|max:200',
            'age_max' => 'nullable|integer|min:0|max:200',
            'ref_range_low' => 'nullable|string|max:50',
            'ref_range_high' => 'nullable|string|max:50',
            'ref_range_text' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'loinc_code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $validated['created_by'] = Auth::id();
            LabTest::create($validated);

            return redirect()->route('lab-tests.index')
                ->with('success', 'Tes laboratorium berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambah tes lab: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan tes laboratorium');
        }
    }

    public function show(LabTest $labTest): View
    {
        $labTest->load('category');

        return view('lab-tests.show', compact('labTest'));
    }

    public function edit(LabTest $labTest): View
    {
        $categories = LabTestCategory::where('is_active', true)->orderBy('name')->get();

        return view('lab-tests.edit', compact('labTest', 'categories'));
    }

    public function update(Request $request, LabTest $labTest): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:lab_test_categories,id',
            'code' => 'required|string|max:20|unique:lab_tests,code,' . $labTest->id,
            'name' => 'required|string|max:255',
            'specimen_type' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:30',
            'gender' => 'nullable|in:L,P',
            'age_min' => 'nullable|integer|min:0|max:200',
            'age_max' => 'nullable|integer|min:0|max:200',
            'ref_range_low' => 'nullable|string|max:50',
            'ref_range_high' => 'nullable|string|max:50',
            'ref_range_text' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'loinc_code' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $labTest->update($validated);

            return redirect()->route('lab-tests.show', $labTest)
                ->with('success', 'Tes laboratorium berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal update tes lab: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui tes laboratorium');
        }
    }

    public function destroy(LabTest $labTest): RedirectResponse
    {
        try {
            $labTest->delete();

            return redirect()->route('lab-tests.index')
                ->with('success', 'Tes laboratorium berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal hapus tes lab: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus tes laboratorium');
        }
    }
}
