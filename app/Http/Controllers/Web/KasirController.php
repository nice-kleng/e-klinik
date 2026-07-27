<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\MedicalRecordProcedure;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\Registration;
use App\Models\Polyclinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KasirController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('role:admin|cashier');
    // }

    public function index(Request $request): View
    {
        $query = Invoice::with(['patient', 'registration', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $pendingCount = Invoice::where('status', 'pending')->count();

        return view('kasir.index', compact('invoices', 'pendingCount'));
    }

    public function create(Request $request): View
    {
        if ($request->filled('registration_id')) {
            $registration = Registration::with(['patient', 'polyclinic', 'doctor'])
                ->findOrFail($request->registration_id);
            $patient = $registration->patient;
            $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();

            $prescriptions = Prescription::with(['items.medicine'])
                ->where('patient_id', $patient->id)
                ->whereIn('status', ['active', 'dispensed'])
                ->whereHas('medicalRecord', function ($q) use ($registration) {
                    $q->where('registration_id', $registration->id);
                })
                ->get();

            $procedures = MedicalRecordProcedure::with('medicalRecord')
                ->whereHas('medicalRecord', function ($q) use ($registration) {
                    $q->where('registration_id', $registration->id);
                })
                ->get();

            $labRequests = LabRequest::with(['items.test'])
                ->where('patient_id', $patient->id)
                ->whereHas('registration', function ($q) use ($registration) {
                    $q->where('id', $registration->id);
                })
                ->get();

            return view('kasir.create', compact(
                'registration', 'patient', 'polyclinics',
                'prescriptions', 'procedures', 'labRequests'
            ));
        }

        $registrations = Registration::with(['patient', 'polyclinic'])
            ->whereIn('service_status', ['pharmacy', 'cashier'])
            ->whereDate('registration_date', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kasir.create', compact('registrations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|string',
            'items.*.itemable_type' => 'required|string',
            'items.*.itemable_id' => 'required|integer',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $registration = Registration::with('patient')->findOrFail($validated['registration_id']);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'registration_id' => $registration->id,
                'patient_id' => $registration->patient_id,
                'doctor_id' => $registration->doctor_id,
                'polyclinic_id' => $registration->polyclinic_id,
                'user_id' => auth()->id(),
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $total += $subtotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => $item['item_type'],
                    'itemable_type' => $item['itemable_type'],
                    'itemable_id' => $item['itemable_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $invoice->update(['total_amount' => $total]);

            DB::commit();

            return redirect()->route('kasir.show', $invoice)
                ->with('success', 'Invoice berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat invoice: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal membuat invoice');
        }
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'registration', 'doctor', 'polyclinic', 'user', 'items']);

        return view('kasir.show', compact('invoice'));
    }

    public function pay(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,debit,credit,transfer,bpjs,other',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        if ($invoice->status !== 'pending') {
            return redirect()->back()->with('error', 'Invoice sudah dibayar atau dibatalkan');
        }

        $paidAmount = (float) $validated['paid_amount'];
        $totalAmount = (float) $invoice->total_amount;

        if ($paidAmount < $totalAmount) {
            return redirect()->back()->with('error', 'Jumlah pembayaran kurang dari total tagihan');
        }

        $changeAmount = $paidAmount - $totalAmount;

        $invoice->update([
            'status' => 'paid',
            'paid_amount' => $paidAmount,
            'change_amount' => $changeAmount,
            'payment_method' => $validated['payment_method'],
            'paid_at' => now(),
        ]);

        $registration = $invoice->registration;
        if ($registration && $registration->service_status === 'cashier') {
            $registration->update(['service_status' => 'education']);
        }

        return redirect()->route('kasir.show', $invoice)
            ->with('success', 'Pembayaran berhasil. Kembalian: Rp ' . number_format($changeAmount, 0, ',', '.'));
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya invoice pending yang bisa dibatalkan');
        }

        $invoice->items()->delete();
        $invoice->update(['status' => 'cancelled']);

        return redirect()->route('kasir.index')
            ->with('success', 'Invoice dibatalkan');
    }

    public function print(Invoice $invoice): View
    {
        $invoice->load(['patient', 'registration', 'doctor', 'polyclinic', 'user', 'items']);

        return view('kasir.print', compact('invoice'));
    }
}
