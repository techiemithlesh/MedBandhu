<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $doctors = Staff::doctors()
            ->with(['doctorProfile', 'department', 'branch'])
            ->when($request->filled('department'), fn ($q) => $q->where('department_id', $request->integer('department')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(fn ($w) => $w->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term));
            })
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('doctors.index', [
            'doctors' => $doctors,
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function show(Staff $doctor): View
    {
        abort_unless($doctor->is_doctor, 404);

        $doctor->load(['doctorProfile', 'department', 'branch', 'schedules.branch', 'user']);

        return view('doctors.show', ['doctor' => $doctor]);
    }
}
