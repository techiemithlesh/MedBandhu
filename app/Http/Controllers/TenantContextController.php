<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TenantContextController extends Controller
{
    public function switchBranch(Request $request): RedirectResponse
    {
        $data = $request->validate(['branch_id' => ['required', 'integer']]);

        $branch = $request->user()->branches()->find($data['branch_id']);

        if (! $branch) {
            throw ValidationException::withMessages(['branch_id' => 'You do not have access to that branch.']);
        }

        $request->session()->put('active_branch_id', $branch->id);

        return back()->with('status', "Switched to {$branch->name}.");
    }

    public function enterHospital(Request $request, Hospital $hospital): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $request->session()->put('acting_hospital_id', $hospital->id);
        $request->session()->forget('active_branch_id');

        return redirect()->route('dashboard')->with('status', "Now working in {$hospital->name}.");
    }

    public function leaveHospital(Request $request): RedirectResponse
    {
        $request->session()->forget(['acting_hospital_id', 'active_branch_id']);

        return redirect()->route('dashboard')->with('status', 'Back to platform view.');
    }
}
