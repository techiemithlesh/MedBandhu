<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('platform.plans.index', [
            'plans' => Plan::withCount('subscriptions')->orderBy('sort_order')->orderBy('price_yearly')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Plan([
            'trial_days' => 14, 'branch_limit' => 1, 'is_active' => true, 'is_public' => true,
            'features' => ['ai' => false, 'custom_domain' => false, 'sms' => false, 'priority_support' => false],
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        Plan::create($this->validated($request));

        return redirect()->route('platform.plans.index')->with('status', 'Plan created.');
    }

    public function edit(Plan $plan): View
    {
        return $this->form($plan);
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validated($request, $plan));

        return redirect()->route('platform.plans.index')->with('status', 'Plan updated.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', 'Plan has active subscriptions.');
        }
        $plan->delete();

        return back()->with('status', 'Plan removed.');
    }

    protected function form(Plan $plan): View
    {
        return view('platform.plans.form', [
            'plan' => $plan,
            'allModules' => config('hms.modules'),
        ]);
    }

    protected function validated(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'alpha_dash', Rule::unique('plans', 'code')->ignore($plan?->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'price_monthly' => ['required', 'numeric', 'min:0'],
            'price_half_yearly' => ['required', 'numeric', 'min:0'],
            'price_yearly' => ['required', 'numeric', 'min:0'],
            'price_extra_branch' => ['required', 'numeric', 'min:0'],
            'price_perpetual' => ['nullable', 'numeric', 'min:0'],
            'price_amc' => ['nullable', 'numeric', 'min:0'],
            'branch_limit' => ['required', 'integer', 'min:1', 'max:200'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:90'],
            'modules' => ['nullable', 'array'],
            'modules.*' => [Rule::in(config('hms.modules'))],
            'features' => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['code'] = Str::lower($data['code']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_public'] = $request->boolean('is_public');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        // empty modules array => all modules (store null)
        $data['modules'] = empty($data['modules']) ? null : array_values($data['modules']);
        $data['features'] = collect(['ai', 'custom_domain', 'sms', 'priority_support'])
            ->mapWithKeys(fn ($f) => [$f => $request->boolean("features.$f")])->all();

        return $data;
    }
}
