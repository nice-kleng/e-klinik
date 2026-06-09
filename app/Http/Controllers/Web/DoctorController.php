<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Polyclinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        $doctors = Doctor::with('polyclinic')
            ->orderBy('name')
            ->paginate(15);

        return view('doctors.index', compact('doctors'));
    }

    public function create(): View
    {
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();

        return view('doctors.create', compact('polyclinics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'code' => 'required|string|max:10|unique:doctors,code',
            'name' => 'required|string|max:255',
            'specialist' => 'nullable|string|max:255',
            'sip_number' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            Doctor::create($validated);

            return redirect()->route('doctors.index')
                ->with('success', 'Dokter berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan dokter: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan dokter');
        }
    }

    public function show(Doctor $doctor): View
    {
        $doctor->load(['polyclinic', 'queues']);

        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor): View
    {
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();

        return view('doctors.edit', compact('doctor', 'polyclinics'));
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'code' => 'required|string|max:10|unique:doctors,code,' . $doctor->id,
            'name' => 'required|string|max:255',
            'specialist' => 'nullable|string|max:255',
            'sip_number' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $doctor->update($validated);

            return redirect()->route('doctors.show', $doctor)
                ->with('success', 'Data dokter berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui dokter: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data dokter');
        }
    }
}
