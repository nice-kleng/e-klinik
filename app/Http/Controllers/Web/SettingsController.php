<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = Configuration::whereIn('group', ['pharmacy', 'billing'])
            ->get()
            ->keyBy('key');

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_calc' => 'nullable|boolean',
            'tuslah' => 'required|integer|min:0|max:99999999',
            'embalase' => 'required|integer|min:0|max:99999999',
            'biaya_konsultasi' => 'required|integer|min:0|max:99999999',
        ]);

        Configuration::updateOrCreate(
            ['group' => 'pharmacy', 'key' => 'auto_calc'],
            ['value' => $validated['auto_calc'] ? 'true' : 'false', 'data_type' => 'boolean']
        );

        Configuration::updateOrCreate(
            ['group' => 'pharmacy', 'key' => 'tuslah'],
            ['value' => (string) $validated['tuslah'], 'data_type' => 'integer']
        );

        Configuration::updateOrCreate(
            ['group' => 'pharmacy', 'key' => 'embalase'],
            ['value' => (string) $validated['embalase'], 'data_type' => 'integer']
        );

        Configuration::updateOrCreate(
            ['group' => 'billing', 'key' => 'biaya_konsultasi'],
            ['value' => (string) $validated['biaya_konsultasi'], 'data_type' => 'integer']
        );

        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan');
    }
}
