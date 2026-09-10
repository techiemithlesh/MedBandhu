<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request, Tenancy $tenancy): View
    {
        $staff = Staff::query()
            ->with(['department', 'branch', 'user'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('department'), fn ($q) => $q->where('department_id', $request->integer('department')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('employee_code', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('staff.index', [
            'staff' => $staff,
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'types' => Staff::TYPES,
        ]);
    }

    public function create(Tenancy $tenancy): View
    {
        return $this->form(new Staff([
            'type' => 'nurse',
            'status' => 'active',
            'employment_type' => 'permanent',
            'branch_id' => $tenancy->branchId(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateStaff($request);

        $staff = DB::transaction(function () use ($request, $data) {
            $staff = Staff::create($this->staffAttributes($request, $data));

            $this->syncDoctorProfile($staff, $request);
            $this->maybeCreateLogin($staff, $request);

            return $staff;
        });

        return redirect()->route('staff.show', $staff)->with('status', "{$staff->full_name} added.");
    }

    public function show(Staff $staff): View
    {
        $staff->load(['department', 'branch', 'user', 'doctorProfile', 'schedules.branch']);

        return view('staff.show', compact('staff'));
    }

    public function edit(Staff $staff): View
    {
        return $this->form($staff);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $data = $this->validateStaff($request, $staff);

        DB::transaction(function () use ($request, $staff, $data) {
            $staff->update($this->staffAttributes($request, $data, $staff));
            $this->syncDoctorProfile($staff, $request);
            $this->maybeCreateLogin($staff, $request);
        });

        return redirect()->route('staff.show', $staff)->with('status', 'Staff record updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->delete();

        return redirect()->route('staff.index')->with('status', 'Staff record archived.');
    }

    // -------------------------------------------------------------------

    protected function form(Staff $staff): View
    {
        return view('staff.form', [
            'staff' => $staff,
            'departments' => Department::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'branches' => auth()->user()->branches->pluck('name', 'id'),
            'types' => Staff::TYPES,
            'assignableRoles' => Role::where('team_id', app(Tenancy::class)->hospitalId())
                ->where('name', '!=', 'Hospital Admin')
                ->orderBy('name')->pluck('name', 'name'),
        ]);
    }

    protected function validateStaff(Request $request, ?Staff $staff = null): array
    {
        $hospitalId = app(Tenancy::class)->hospitalId();

        return $request->validate([
            'employee_code' => [
                'required', 'string', 'max:30',
                Rule::unique('staff', 'employee_code')->where('hospital_id', $hospitalId)->ignore($staff?->id),
            ],
            'type' => ['required', Rule::in(array_keys(Staff::TYPES))],
            'branch_id' => ['required', Rule::in(auth()->user()->branches->pluck('id')->all())],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('hospital_id', $hospitalId)],
            'salutation' => ['nullable', 'string', 'max:10'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'dob' => ['nullable', 'date', 'before:today'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'phone' => ['nullable', 'string', 'max:20'],
            'alt_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'designation' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', Rule::in(['permanent', 'contract', 'visiting', 'intern'])],
            'joined_on' => ['nullable', 'date'],
            'left_on' => ['nullable', 'date', 'after_or_equal:joined_on'],
            'status' => ['required', Rule::in(['active', 'on_leave', 'suspended', 'resigned'])],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:2048'],

            // doctor profile (validated only when type = doctor — see rules below)
            'doctor.specialization' => ['nullable', 'string', 'max:255'],
            'doctor.qualifications' => ['nullable', 'string', 'max:255'],
            'doctor.registration_no' => ['nullable', 'string', 'max:100'],
            'doctor.registration_council' => ['nullable', 'string', 'max:255'],
            'doctor.experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'doctor.consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'doctor.followup_fee' => ['nullable', 'numeric', 'min:0'],
            'doctor.followup_valid_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'doctor.appointment_duration_min' => ['nullable', 'integer', 'min:5', 'max:120'],
            'doctor.is_surgeon' => ['boolean'],
            'doctor.online_consultation' => ['boolean'],
            'doctor.bio' => ['nullable', 'string'],

            // optional login account
            'create_login' => ['boolean'],
            'login_role' => ['nullable', 'required_if:create_login,1', Rule::in(
                Role::where('team_id', $hospitalId)->pluck('name')->all()
            )],
            'login_password' => ['nullable', 'required_if:create_login,1', 'string', 'min:8'],
        ]);
    }

    protected function staffAttributes(Request $request, array $data, ?Staff $staff = null): array
    {
        $attributes = collect($data)->except(['doctor', 'photo', 'create_login', 'login_role', 'login_password'])->all();

        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('staff-photos', 'public');
        }

        return $attributes;
    }

    protected function syncDoctorProfile(Staff $staff, Request $request): void
    {
        if ($staff->type !== 'doctor') {
            $staff->doctorProfile()->delete();

            return;
        }

        $d = $request->input('doctor', []);

        $staff->doctorProfile()->updateOrCreate([], [
            'specialization' => $d['specialization'] ?? null,
            'qualifications' => $d['qualifications'] ?? null,
            'registration_no' => $d['registration_no'] ?? null,
            'registration_council' => $d['registration_council'] ?? null,
            'experience_years' => $d['experience_years'] ?? null,
            'consultation_fee' => $d['consultation_fee'] ?? 0,
            'followup_fee' => $d['followup_fee'] ?? 0,
            'followup_valid_days' => $d['followup_valid_days'] ?? 7,
            'appointment_duration_min' => $d['appointment_duration_min'] ?? 15,
            'is_surgeon' => $request->boolean('doctor.is_surgeon'),
            'online_consultation' => $request->boolean('doctor.online_consultation'),
            'bio' => $d['bio'] ?? null,
        ]);
    }

    protected function maybeCreateLogin(Staff $staff, Request $request): void
    {
        if (! $request->boolean('create_login') || $staff->user_id) {
            return;
        }

        $email = $staff->email ?: Str::slug($staff->full_name, '.').'@'.app(Tenancy::class)->hospital()->slug.'.hms';

        $user = User::create([
            'hospital_id' => $staff->hospital_id,
            'name' => $staff->full_name,
            'email' => $email,
            'phone' => $staff->phone,
            'designation' => $staff->designation,
            'password' => $request->string('login_password'),
            'is_active' => true,
        ]);

        setPermissionsTeamId($staff->hospital_id);
        $user->assignRole($request->string('login_role'));
        $user->branches()->syncWithoutDetaching([$staff->branch_id => ['is_primary' => true]]);

        $staff->update(['user_id' => $user->id]);
    }
}
