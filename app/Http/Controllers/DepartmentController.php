<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        return view('departments.index', [
            'departments' => Department::withCount('staff')->with('head')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('departments.form', [
            'department' => new Department(['is_active' => true]),
            'heads' => Staff::active()->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Department::create($data);

        return redirect()->route('departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department): View
    {
        return view('departments.form', [
            'department' => $department,
            'heads' => Staff::active()->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $department->update($this->validated($request, $department));

        return redirect()->route('departments.index')->with('status', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->staff()->exists()) {
            return back()->with('error', 'Cannot delete a department that still has staff assigned.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('status', 'Department deleted.');
    }

    protected function validated(Request $request, ?Department $department = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('departments', 'code')
                    ->where('hospital_id', app(\App\Support\Tenancy::class)->hospitalId())
                    ->ignore($department?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'head_staff_id' => ['nullable', Rule::exists('staff', 'id')],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = Str::upper($data['code']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
