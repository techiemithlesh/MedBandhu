<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Ward;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WardController extends Controller
{
    public function index(Tenancy $tenancy): View
    {
        $wards = Ward::with('department')
            ->withCount([
                'beds',
                'beds as available_beds_count' => fn ($q) => $q->where('status', 'available'),
                'beds as occupied_beds_count' => fn ($q) => $q->where('status', 'occupied'),
            ])
            ->where('branch_id', $tenancy->branchId())
            ->orderBy('name')
            ->get();

        return view('wards.index', compact('wards'));
    }

    public function create(): View
    {
        return $this->form(new Ward(['type' => 'general', 'gender_restriction' => 'any', 'is_active' => true]));
    }

    public function store(Request $request, Tenancy $tenancy): RedirectResponse
    {
        $data = $this->validated($request);
        $data['branch_id'] = $tenancy->branchId();

        $ward = Ward::create($data);

        return redirect()->route('wards.beds', $ward)->with('status', 'Ward created — now add beds.');
    }

    public function edit(Ward $ward): View
    {
        return $this->form($ward);
    }

    public function update(Request $request, Ward $ward): RedirectResponse
    {
        $ward->update($this->validated($request, $ward));

        return redirect()->route('wards.index')->with('status', 'Ward updated.');
    }

    public function destroy(Ward $ward): RedirectResponse
    {
        if ($ward->beds()->whereHas('currentAdmission')->exists()) {
            return back()->with('error', 'Cannot delete a ward with occupied beds.');
        }

        $ward->beds()->delete();
        $ward->delete();

        return redirect()->route('wards.index')->with('status', 'Ward deleted.');
    }

    protected function form(Ward $ward): View
    {
        return view('wards.form', [
            'ward' => $ward,
            'types' => Ward::TYPES,
            'departments' => Department::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    protected function validated(Request $request, ?Ward $ward = null): array
    {
        $hospitalId = app(Tenancy::class)->hospitalId();
        $branchId = app(Tenancy::class)->branchId();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('wards', 'code')->where('branch_id', $branchId)->ignore($ward?->id),
            ],
            'type' => ['required', Rule::in(array_keys(Ward::TYPES))],
            'department_id' => ['nullable', Rule::exists('departments', 'id')->where('hospital_id', $hospitalId)],
            'floor' => ['nullable', 'string', 'max:40'],
            'gender_restriction' => ['required', Rule::in(['any', 'male', 'female'])],
            'default_daily_charge' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = Str::upper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
