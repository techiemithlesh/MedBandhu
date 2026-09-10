<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BedController extends Controller
{
    public function index(Ward $ward): View
    {
        $ward->load(['beds.currentAdmission.patient']);

        return view('wards.beds', compact('ward'));
    }

    public function store(Request $request, Ward $ward): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['single', 'bulk'])],
            'bed_number' => ['required_if:mode,single', 'nullable', 'string', 'max:20'],
            'prefix' => ['required_if:mode,bulk', 'nullable', 'string', 'max:12'],
            'count' => ['required_if:mode,bulk', 'nullable', 'integer', 'min:1', 'max:60'],
            'start_from' => ['nullable', 'integer', 'min:1'],
            'room_label' => ['nullable', 'string', 'max:40'],
            'daily_charge' => ['nullable', 'numeric', 'min:0'],
        ]);

        $charge = $data['daily_charge'] ?? $ward->default_daily_charge;
        $existing = $ward->beds()->pluck('bed_number')->flip();
        $created = 0;

        $numbers = $data['mode'] === 'single'
            ? [$data['bed_number']]
            : collect(range($data['start_from'] ?? 1, ($data['start_from'] ?? 1) + $data['count'] - 1))
                ->map(fn ($n) => trim(($data['prefix'] ?? '').$n))->all();

        foreach ($numbers as $number) {
            if ($existing->has($number)) {
                continue;
            }
            $ward->beds()->create([
                'hospital_id' => $ward->hospital_id,
                'branch_id' => $ward->branch_id,
                'bed_number' => $number,
                'room_label' => $data['room_label'] ?? null,
                'daily_charge' => $charge,
                'status' => 'available',
            ]);
            $created++;
        }

        return back()->with('status', "{$created} bed(s) added.");
    }

    public function update(Request $request, Bed $bed): RedirectResponse
    {
        $data = $request->validate([
            'bed_number' => [
                'required', 'string', 'max:20',
                Rule::unique('beds', 'bed_number')->where('ward_id', $bed->ward_id)->ignore($bed->id),
            ],
            'room_label' => ['nullable', 'string', 'max:40'],
            'daily_charge' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(Bed::STATUSES)],
            'is_active' => ['boolean'],
        ]);

        if ($bed->currentAdmission && $data['status'] !== 'occupied') {
            return back()->with('error', 'This bed has an active admission — discharge or transfer the patient first.');
        }

        $bed->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Bed updated.');
    }

    public function destroy(Bed $bed): RedirectResponse
    {
        if ($bed->currentAdmission) {
            return back()->with('error', 'Cannot delete an occupied bed.');
        }

        $wardId = $bed->ward_id;
        $bed->delete();

        return redirect()->route('wards.beds', $wardId)->with('status', 'Bed removed.');
    }
}
