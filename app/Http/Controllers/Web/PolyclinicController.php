<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Polyclinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PolyclinicController extends Controller
{
    public function index(): View
    {
        $polyclinics = Polyclinic::withCount('doctors')
            ->orderBy('name')
            ->paginate(15);

        return view('polyclinics.index', compact('polyclinics'));
    }

    public function create(): View
    {
        return view('polyclinics.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:polyclinics,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            Polyclinic::create($validated);

            return redirect()->route('polyclinics.index')
                ->with('success', 'Poli berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan poli: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan poli');
        }
    }

    public function show(Polyclinic $polyclinic): View
    {
        $polyclinic->load(['doctors', 'queues']);

        return view('polyclinics.show', compact('polyclinic'));
    }

    public function edit(Polyclinic $polyclinic): View
    {
        return view('polyclinics.edit', compact('polyclinic'));
    }

    public function update(Request $request, Polyclinic $polyclinic): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:polyclinics,code,' . $polyclinic->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $polyclinic->update($validated);

            return redirect()->route('polyclinics.show', $polyclinic)
                ->with('success', 'Data poli berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui poli: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data poli');
        }
    }
}
