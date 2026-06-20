<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalRecordAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(Request $request): View
    {
        $query = MedicalRecordAudit::with(['medicalRecord.patient', 'user'])
            ->latest('medical_record_audits.created_at');

        if ($request->filled('start_date')) {
            $query->whereDate('medical_record_audits.created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('medical_record_audits.created_at', '<=', $request->end_date);
        }

        if ($request->filled('action')) {
            $query->where('medical_record_audits.action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('medical_record_audits.user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('medicalRecord.patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('no_rm', 'like', "%{$search}%");
            });
        }

        if (auth()->user()->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if ($doctor) {
                $query->whereHas('medicalRecord', function ($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            }
        }

        $audits = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get();
        $actions = ['created', 'updated', 'deleted', 'restored'];

        return view('audit-trail.index', compact('audits', 'users', 'actions'));
    }
}
