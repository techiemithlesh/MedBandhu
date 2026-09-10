<?php

namespace App\Http\Controllers;

use App\Models\IpdAdmission;
use App\Models\IpdCharge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IpdChargeController extends Controller
{
    public function store(Request $request, IpdAdmission $admission): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['service', 'procedure', 'consumable', 'other'])],
            'charge_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
        ]);

        $admission->charges()->create($data + ['auto_generated' => false]);

        return back()->with('status', 'Charge added.');
    }

    public function destroy(IpdAdmission $admission, IpdCharge $charge): RedirectResponse
    {
        abort_unless($charge->ipd_admission_id === $admission->id, 404);
        abort_if($charge->auto_generated, 422, 'Auto-generated bed charges cannot be deleted here.');

        $charge->delete();

        return back()->with('status', 'Charge removed.');
    }
}
