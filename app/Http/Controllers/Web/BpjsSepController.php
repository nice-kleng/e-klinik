<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BpjsSep;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Services\BpjsSepService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BpjsSepController extends Controller
{
    protected BpjsSepService $bpjsSepService;

    public function __construct(BpjsSepService $bpjsSepService)
    {
        $this->bpjsSepService = $bpjsSepService;
    }

    public function index(Request $request): View
    {
        $query = BpjsSep::with(['patient', 'queue.polyclinic', 'creator']);

        if ($request->filled('no_sep')) {
            $query->where('no_sep', 'like', '%' . $request->no_sep . '%');
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('tgl_awal')) {
            $query->where('tgl_pelayanan', '>=', $request->tgl_awal);
        }

        if ($request->filled('tgl_akhir')) {
            $query->where('tgl_pelayanan', '<=', $request->tgl_akhir);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $seps = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        $patients = Patient::orderBy('name')->get(['id', 'name', 'no_rm']);

        return view('bpjs-seps.index', compact('seps', 'patients'));
    }

    public function create(): View
    {
        $patients = Patient::where('insurance_type', 'BPJS')
            ->with('bpjsPatient')
            ->orderBy('name')
            ->get();

        $queues = Queue::with(['registration.patient', 'polyclinic'])
            ->whereHas('registration.patient', fn ($q) => $q->where('insurance_type', 'BPJS'))
            ->where('queue_date', now()->toDateString())
            ->orderBy('id', 'desc')
            ->get();

        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();

        return view('bpjs-seps.create', compact('patients', 'queues', 'polyclinics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'queue_id' => 'nullable|exists:queues,id',
            'tgl_pelayanan' => 'required|date',
            'kode_poli' => 'required|string',
            'kode_dokter' => 'nullable|string',
            'diagnosa' => 'nullable|string',
            'no_rujukan' => 'nullable|string',
            'catatan' => 'nullable|string',
        ]);

        try {
            $patient = Patient::with('bpjsPatient')->findOrFail($validated['patient_id']);

            if (!$patient->bpjsPatient || !$patient->bpjsPatient->no_kartu) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Pasien BPJS tidak memiliki nomor kartu');
            }

            $dataSep = [
                'noKartu' => $patient->bpjsPatient->no_kartu,
                'tglPelayanan' => $validated['tgl_pelayanan'],
                'kodePoli' => $validated['kode_poli'],
                'kodeDokter' => $validated['kode_dokter'] ?? '',
                'diagnosa' => $validated['diagnosa'] ?? '',
            ];

            $response = app(\App\Services\BPJS\VClaimService::class)->insertSep($dataSep);

            if (!$response || !isset($response['sep']['noSep'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Gagal membuat SEP: response tidak valid dari BPJS');
            }

            BpjsSep::create([
                'patient_id' => $patient->id,
                'queue_id' => $validated['queue_id'],
                'no_sep' => $response['sep']['noSep'],
                'no_kartu' => $patient->bpjsPatient->no_kartu,
                'tgl_pelayanan' => $validated['tgl_pelayanan'],
                'kode_poli' => $validated['kode_poli'],
                'kode_dokter' => $validated['kode_dokter'] ?? '',
                'diagnosa' => $validated['diagnosa'] ?? '',
                'no_rujukan' => $validated['no_rujukan'] ?? '',
                'catatan' => $validated['catatan'] ?? '',
                'response_raw' => $response,
                'created_by' => auth()->id(),
            ]);

            if ($validated['queue_id']) {
                $queue = Queue::find($validated['queue_id']);
                if ($queue && $queue->registration) {
                    $queue->registration->update(['no_sep' => $response['sep']['noSep']]);
                }
            }

            return redirect()->route('bpjs-seps.index')
                ->with('success', 'SEP berhasil dibuat (No: ' . $response['sep']['noSep'] . ')');
        } catch (\Exception $e) {
            Log::error('Gagal membuat SEP manual: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat SEP: ' . $e->getMessage());
        }
    }

    public function show(BpjsSep $bpjsSep): View
    {
        $bpjsSep->load(['patient', 'queue.polyclinic', 'queue.registration.doctor', 'creator']);

        $responseDetail = null;
        try {
            $responseDetail = $this->bpjsSepService->getSep($bpjsSep->no_sep);
        } catch (\Exception $e) {
            Log::warning('Gagal fetch SEP detail dari BPJS', ['no_sep' => $bpjsSep->no_sep]);
        }

        return view('bpjs-seps.show', compact('bpjsSep', 'responseDetail'));
    }

    public function destroy(BpjsSep $bpjsSep): RedirectResponse
    {
        try {
            $this->bpjsSepService->deleteSep($bpjsSep);

            return redirect()->route('bpjs-seps.index')
                ->with('success', 'SEP ' . $bpjsSep->no_sep . ' berhasil dinonaktifkan');
        } catch (\Exception $e) {
            Log::error('Gagal hapus SEP: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus SEP: ' . $e->getMessage());
        }
    }
}
