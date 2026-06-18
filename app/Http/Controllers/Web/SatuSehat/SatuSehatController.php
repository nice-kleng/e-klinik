<?php

namespace App\Http\Controllers\Web\SatuSehat;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Polyclinic;
use App\Models\SatusehatLog;
use App\Models\SatusehatResource;
use App\Services\SatuSehat\AuthService;
use App\Services\SatuSehat\LocationService;
use App\Services\SatuSehat\OrganizationService;
use App\Services\SatuSehat\PractitionerService;
use App\Services\SatuSehat\TerminologyService;
use Illuminate\Http\Request;

class SatuSehatController extends Controller
{
    protected OrganizationService $organizationService;
    protected LocationService $locationService;
    protected PractitionerService $practitionerService;
    protected TerminologyService $terminologyService;

    public function __construct()
    {
        $this->organizationService = app(OrganizationService::class);
        $this->locationService = app(LocationService::class);
        $this->practitionerService = app(PractitionerService::class);
        $this->terminologyService = app(TerminologyService::class);
    }

    public function index()
    {
        $stats = [
            'patients' => [
                'total' => SatusehatResource::where('resource_type', 'Patient')->count(),
                'synced' => SatusehatResource::where('resource_type', 'Patient')->where('status', 'synced')->count(),
            ],
            'locations' => [
                'total' => SatusehatResource::where('resource_type', 'Location')->count(),
                'synced' => SatusehatResource::where('resource_type', 'Location')->where('status', 'synced')->count(),
            ],
            'practitioners' => [
                'total' => SatusehatResource::where('resource_type', 'Practitioner')->count(),
                'synced' => SatusehatResource::where('resource_type', 'Practitioner')->where('status', 'synced')->count(),
            ],
            'encounters' => [
                'total' => SatusehatResource::where('resource_type', 'Encounter')->count(),
                'synced' => SatusehatResource::where('resource_type', 'Encounter')->where('status', 'synced')->count(),
            ],
            'conditions' => [
                'total' => SatusehatResource::where('resource_type', 'Condition')->count(),
                'synced' => SatusehatResource::where('resource_type', 'Condition')->where('status', 'synced')->count(),
            ],
        ];

        $orgId = config('satusehat.organization_id');
        $token = null;
        try {
            $token = app(AuthService::class)->getAccessToken();
        } catch (\Exception $e) {
            $token = null;
        }

        $logs = SatusehatLog::latest()->take(10)->get();

        return view('satusehat.index', compact('stats', 'orgId', 'token', 'logs'));
    }

    public function organization()
    {
        $orgData = null;
        $error = null;

        try {
            $orgData = $this->organizationService->validateOrganization();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return view('satusehat.organization', compact('orgData', 'error'));
    }

    public function locations()
    {
        $polyclinics = Polyclinic::all();
        $resourceIds = SatusehatResource::where('resource_type', 'Location')
            ->where('model_type', Polyclinic::class)
            ->get()
            ->keyBy('model_id');

        return view('satusehat.locations', compact('polyclinics', 'resourceIds'));
    }

    public function syncLocation(Polyclinic $polyclinic)
    {
        try {
            $result = $this->locationService->syncLocation($polyclinic);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Lokasi berhasil disinkronkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkron: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function practitioners()
    {
        $doctors = Doctor::with('user', 'polyclinic')->get();
        $resourceIds = SatusehatResource::where('resource_type', 'Practitioner')
            ->where('model_type', Doctor::class)
            ->get()
            ->keyBy('model_id');

        return view('satusehat.practitioners', compact('doctors', 'resourceIds'));
    }

    public function syncPractitioner(Doctor $doctor)
    {
        try {
            $result = $this->practitionerService->syncPractitioner($doctor);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Praktisi berhasil disinkronkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkron: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function medicines()
    {
        $medicines = Medicine::with('category')->get();
        $resourceIds = SatusehatResource::where('resource_type', 'MedicationRequest')
            ->where('model_type', Medicine::class)
            ->get()
            ->keyBy('model_id');

        return view('satusehat.medicines', compact('medicines', 'resourceIds'));
    }

    public function searchKfa(Request $request)
    {
        $request->validate(['keyword' => 'required|string|min:2']);

        try {
            $results = $this->terminologyService->searchKfa($request->keyword);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateKfa(Request $request, Medicine $medicine)
    {
        $request->validate([
            'kfa_code' => 'required|string|max:20',
            'kfa_name' => 'nullable|string|max:255',
        ]);

        $medicine->update([
            'kfa_code' => $request->kfa_code,
            'kfa_name' => $request->kfa_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KFA code berhasil disimpan',
        ]);
    }

    public function logs()
    {
        $logs = SatusehatLog::latest()->paginate(50);

        return view('satusehat.logs', compact('logs'));
    }
}
