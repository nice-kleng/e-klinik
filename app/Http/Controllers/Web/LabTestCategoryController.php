<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LabTestCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LabTestCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = LabTestCategory::query();

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('lab-test-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('lab-test-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:lab_test_categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            LabTestCategory::create($validated);

            return redirect()->route('lab-test-categories.index')
                ->with('success', 'Kategori pemeriksaan berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambah kategori lab: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kategori');
        }
    }

    public function edit(LabTestCategory $labTestCategory): View
    {
        return view('lab-test-categories.edit', compact('labTestCategory'));
    }

    public function update(Request $request, LabTestCategory $labTestCategory): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:lab_test_categories,code,' . $labTestCategory->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $labTestCategory->update($validated);

            return redirect()->route('lab-test-categories.index')
                ->with('success', 'Kategori berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal update kategori lab: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kategori');
        }
    }

    public function destroy(LabTestCategory $labTestCategory): RedirectResponse
    {
        try {
            if ($labTestCategory->labTests()->exists()) {
                return redirect()->route('lab-test-categories.index')
                    ->with('error', 'Kategori tidak bisa dihapus karena masih memiliki tes lab');
            }

            $labTestCategory->delete();

            return redirect()->route('lab-test-categories.index')
                ->with('success', 'Kategori berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal hapus kategori lab: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus kategori');
        }
    }
}
