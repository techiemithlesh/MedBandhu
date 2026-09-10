<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Department;
use App\Models\IpdAdmission;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Ward;
use App\Support\IpdService;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IpdAdmissionController extends Controller
{
    public function __construct(protected IpdService $ipd) {}

    public function index(Request $request, Tenancy $tenancy): View
    {
        $admissions = IpdAdmission::query()
            ->with(['patient', 'bed.ward', 'admittingDoctor'])
            ->where('branch_id', $tenancy->branchId())
            ->when($request->input('status', 'admitted') !== 'all',
                fn ($q) => $q->where('status', $request->input('status', 'admitted')))
            ->when($request->filled('ward'), fn ($q) => $q->whereHas('bed', fn ($b) => $b->where('ward_id', $request->integer('ward'))))
            ->latest('admitted_at')
            ->paginate(25)
            ->withQueryString();

        return view('ipd.admissions.index', [
            'admissions' => $admissions,
            'wards' => Ward::where('branch_id', $tenancy->branchId())->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(Request $request, Tenancy $tenancy): View
    {
        return view('ipd.admissions.create', [
            'patient' => $request->filled('patient') ? Patient::findOrFail($request->integer('patient')) : null,
            'presetBed' => $request->filled('bed') ? Bed::available()->find($request->integer('bed')) : null,
            'wards' => $this->wardsWithFreeBeds($tenancy),
            'doctors' => Staff::doctors()->active()->orderBy('first_name')->get()->mapWithKeys(fn ($d) => [$d->id => $d->full_name]),
            'departments' => Department::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => ['required', Rule::exists('patients', 'id')],
            'bed_id' => ['required', Rule::exists('beds', 'id')],
            'admitting_doctor_id' => ['nullable', Rule::exists('staff', 'id')->where('type', 'doctor')],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'appointment_id' => ['nullable', Rule::exists('appointments', 'id')],
            'source' => ['required', Rule::in(['opd', 'emergency', 'direct', 'referral'])],
            'expected_discharge_on' => ['nullable', 'date', 'after_or_equal:today'],
            'provisional_diagnosis' => ['nullable', 'string'],
            'admission_notes' => ['nullable', 'string'],
            'attendant_name' => ['nullable', 'string', 'max:255'],
            'attendant_phone' => ['nullable', 'string', 'max:20'],
            'attendant_relation' => ['nullable', 'string', 'max:40'],
        ]);

        $patient = Patient::findOrFail($data['patient_id']);
        $bed = Bed::findOrFail($data['bed_id']);

        $admission = $this->ipd->admit($patient, $bed, $data);

        return redirect()->route('ipd.admissions.show', $admission)
            ->with('status', "Admitted — {$admission->admission_no}, bed {$bed->label}.");
    }

    public function show(IpdAdmission $admission): View
    {
        $admission->load([
            'patient', 'bed.ward', 'admittingDoctor', 'dischargeDoctor', 'department',
            'bedMovements.ward', 'bedMovements.bed', 'nursingNotes.recorder', 'charges', 'invoices',
        ]);

        return view('ipd.admissions.show', [
            'admission' => $admission,
            'freeBeds' => $this->wardsWithFreeBeds(app(Tenancy::class)),
            'doctors' => Staff::doctors()->active()->orderBy('first_name')->get()->mapWithKeys(fn ($d) => [$d->id => $d->full_name]),
        ]);
    }

    public function transfer(Request $request, IpdAdmission $admission): RedirectResponse
    {
        $data = $request->validate([
            'bed_id' => ['required', Rule::exists('beds', 'id')],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ipd->transfer($admission, Bed::findOrFail($data['bed_id']), $data['reason'] ?? null);

        return back()->with('status', 'Patient transferred.');
    }

    public function discharge(Request $request, IpdAdmission $admission): RedirectResponse
    {
        $data = $request->validate([
            'discharge_type' => ['required', Rule::in(['routine', 'lama', 'referral', 'expired'])],
            'discharged_at' => ['nullable', 'date', 'before_or_equal:now', 'after_or_equal:'.$admission->admitted_at->toDateTimeString()],
            'discharge_summary' => ['nullable', 'string'],
            'discharge_doctor_id' => ['nullable', Rule::exists('staff', 'id')->where('type', 'doctor')],
        ]);

        $this->ipd->discharge($admission, $data);

        return redirect()->route('ipd.admissions.show', $admission)->with('status', 'Patient discharged. Bed charges posted.');
    }

    public function generateCharges(IpdAdmission $admission): RedirectResponse
    {
        $this->ipd->generateBedCharges($admission->load('bedMovements.ward'));

        return back()->with('status', 'Bed charges refreshed.');
    }

    protected function wardsWithFreeBeds(Tenancy $tenancy)
    {
        return Ward::where('branch_id', $tenancy->branchId())
            ->where('is_active', true)
            ->with(['beds' => fn ($q) => $q->available()->orderBy('bed_number')])
            ->orderBy('name')
            ->get()
            ->filter(fn ($w) => $w->beds->isNotEmpty())
            ->values();
    }
}
