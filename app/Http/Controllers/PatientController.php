<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $patients = Patient::query()
            ->search($request->string('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->string('status') === 'active'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function search(Request $request): JsonResponse
    {
        $results = Patient::search($request->string('q'))
            ->where('is_active', true)
            ->limit(10)
            ->get(['id', 'uhid', 'salutation', 'first_name', 'last_name', 'phone', 'gender', 'dob'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'uhid' => $p->uhid,
                'name' => $p->full_name,
                'phone' => $p->phone,
                'age_sex' => trim(($p->age ?? '').($p->gender ? ' · '.ucfirst($p->gender[0]) : '')),
            ]);

        return response()->json(['patients' => $results]);
    }

    public function create(): View
    {
        return view('patients.form', ['patient' => new Patient()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $patient = Patient::create($this->validated($request));

        if ($request->hasFile('photo')) {
            $patient->update(['photo_path' => $request->file('photo')->store('patient-photos', 'public')]);
        }

        return redirect()->route('patients.show', $patient)
            ->with('status', "Patient registered — UHID {$patient->uhid}.");
    }

    public function show(Patient $patient): View
    {
        $patient->load([
            'registeredBranch',
            'appointments.doctor', 'appointments.branch',
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        return view('patients.form', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $patient->update($this->validated($request, $patient));

        if ($request->hasFile('photo')) {
            $patient->update(['photo_path' => $request->file('photo')->store('patient-photos', 'public')]);
        }

        return redirect()->route('patients.show', $patient)->with('status', 'Patient record updated.');
    }

    protected function validated(Request $request, ?Patient $patient = null): array
    {
        $data = $request->validate([
            'salutation' => ['nullable', 'string', 'max:10'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'dob' => ['nullable', 'date', 'before_or_equal:today'],
            'dob_estimated' => ['boolean'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'other'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'alt_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'id_proof_type' => ['nullable', 'string', 'max:40'],
            'id_proof_number' => ['nullable', 'string', 'max:60'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_relation' => ['nullable', 'string', 'max:40'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data['dob_estimated'] = $request->boolean('dob_estimated');
        $data['is_active'] = $patient ? $request->boolean('is_active') : true;
        unset($data['photo']);

        return $data;
    }
}
