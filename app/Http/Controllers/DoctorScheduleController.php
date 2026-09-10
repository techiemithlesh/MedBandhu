<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    public function edit(Staff $doctor): View
    {
        abort_unless($doctor->is_doctor, 404);

        $doctor->load('schedules');

        return view('doctors.schedule', [
            'doctor' => $doctor,
            'branches' => auth()->user()->branches->pluck('name', 'id'),
            'existing' => $doctor->schedules->groupBy('day_of_week'),
        ]);
    }

    public function update(Request $request, Staff $doctor): RedirectResponse
    {
        abort_unless($doctor->is_doctor, 404);

        $branchIds = auth()->user()->branches->pluck('id')->all();

        $data = $request->validate([
            'rows' => ['array'],
            'rows.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'rows.*.branch_id' => ['required', Rule::in($branchIds)],
            'rows.*.start_time' => ['required', 'date_format:H:i'],
            'rows.*.end_time' => ['required', 'date_format:H:i'],
            'rows.*.slot_minutes' => ['required', 'integer', 'min:5', 'max:120'],
            'rows.*.max_tokens' => ['nullable', 'integer', 'min:1', 'max:500'],
            'rows.*.is_active' => ['boolean'],
        ]);

        foreach ($data['rows'] ?? [] as $i => $row) {
            if ($row['end_time'] <= $row['start_time']) {
                return back()->withErrors([
                    "rows.$i.end_time" => 'End time must be after start time for '.\App\Models\DoctorSchedule::DAYS[$row['day_of_week']].'.',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($doctor, $data) {
            $doctor->schedules()->delete();

            foreach ($data['rows'] ?? [] as $row) {
                $doctor->schedules()->create([
                    'branch_id' => $row['branch_id'],
                    'day_of_week' => $row['day_of_week'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'slot_minutes' => $row['slot_minutes'],
                    'max_tokens' => $row['max_tokens'] ?? null,
                    'is_active' => (bool) ($row['is_active'] ?? true),
                ]);
            }
        });

        return redirect()->route('doctors.show', $doctor)->with('status', 'Availability updated.');
    }
}
