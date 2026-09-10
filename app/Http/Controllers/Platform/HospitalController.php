<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Plan;
use App\Support\HospitalProvisioner;
use App\Support\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HospitalController extends Controller
{
    public function index(): View
    {
        return view('platform.hospitals.index', [
            'hospitals' => Hospital::withoutGlobalScopes()
                ->withCount(['branches', 'users'])
                ->with('subscription.plan')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('platform.hospitals.create', [
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request, HospitalProvisioner $provisioner, SubscriptionService $subs): RedirectResponse
    {
        $data = $this->validateHospital($request);
        $plan = Plan::findOrFail($data['plan_id']);

        $hospital = $provisioner->create(
            [
                'name' => $data['name'],
                'code' => $data['code'],
                'slug' => Str::slug($data['name']).'-'.Str::lower($data['code']),
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'settings' => [],
                'branch_limit' => $plan->branch_limit,
            ],
            [
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => $data['admin_password'],
            ]
        );

        $activation = $data['activation'] ?? 'trial';

        $sub = $subs->subscribe($hospital, $plan, [
            'billing_cycle' => $data['billing_cycle'],
            'licence_type' => $data['licence_type'],
            'trial' => $activation === 'trial',
        ]);

        // "Hold for payment" — raise the first invoice and lock access until it's paid.
        if ($activation === 'invoice') {
            $subs->suspend($sub, 'Awaiting first payment');
            $subs->raiseInvoice($sub->load('plan', 'hospital'));
        }

        $note = match ($activation) {
            'invoice' => 'First invoice raised — access unlocks when payment is recorded.',
            'active' => 'Activated.',
            default => '14-day trial started.',
        };

        return redirect()
            ->route('platform.hospitals.show', $hospital)
            ->with('status', "{$hospital->name} created on the {$plan->name} plan. {$note}");
    }

    public function show(Hospital $hospital): View
    {
        $hospital->loadCount(['branches', 'users']);
        $hospital->load(['branches', 'users', 'subscription.plan', 'platformInvoices.payments']);

        return view('platform.hospitals.show', [
            'hospital' => $hospital,
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function edit(Hospital $hospital): View
    {
        return view('platform.hospitals.edit', [
            'hospital' => $hospital,
            'modules' => config('hms.modules'),
        ]);
    }

    public function update(Request $request, Hospital $hospital): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'custom_domain' => ['nullable', 'string', 'max:255', Rule::unique('hospitals', 'custom_domain')->ignore($hospital->id)],
            'is_active' => ['boolean'],
            'modules' => ['array'],
            'modules.*' => [Rule::in(config('hms.modules'))],
        ]);

        $hospital->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
            'settings' => [...($hospital->settings ?? []), 'modules' => $data['modules'] ?? []],
        ]);

        return redirect()->route('platform.hospitals.show', $hospital)->with('status', 'Hospital updated.');
    }

    public function destroy(Hospital $hospital): RedirectResponse
    {
        $hospital->delete();

        return redirect()->route('platform.hospitals.index')->with('status', "{$hospital->name} archived.");
    }

    protected function validateHospital(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20', 'alpha_num', Rule::unique('hospitals', 'code')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'plan_id' => ['required', Rule::exists('plans', 'id')],
            'billing_cycle' => ['required', Rule::in(['monthly', 'half_yearly', 'yearly'])],
            'licence_type' => ['required', Rule::in(['subscription', 'perpetual'])],
            'activation' => ['nullable', Rule::in(['trial', 'invoice', 'active'])],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);
    }
}
