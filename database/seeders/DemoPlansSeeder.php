<?php

namespace Database\Seeders;

use App\Models\Hospital;
use App\Models\Plan;
use App\Support\SubscriptionService;
use Illuminate\Database\Seeder;

class DemoPlansSeeder extends Seeder
{
    public function run(): void
    {
        $allFeaturesOff = ['ai' => false, 'custom_domain' => false, 'sms' => false, 'priority_support' => false];

        $plans = [
            [
                'code' => 'clinic', 'name' => 'Clinic', 'sort_order' => 1, 'is_public' => true,
                'description' => 'OPD, pharmacy & billing for a single clinic',
                'price_monthly' => 799, 'price_half_yearly' => 4499, 'price_yearly' => 7999,
                'price_extra_branch' => 0, 'price_perpetual' => 29999, 'price_amc' => 7999,
                'branch_limit' => 1, 'trial_days' => 14,
                'modules' => ['patients', 'appointments', 'opd', 'pharmacy', 'billing', 'reports'],
                'features' => $allFeaturesOff,
            ],
            [
                'code' => 'hospital', 'name' => 'Hospital', 'sort_order' => 2, 'is_public' => true,
                'description' => 'Everything, including IPD & bed management. Add branches as you grow.',
                'price_monthly' => 1499, 'price_half_yearly' => 8499, 'price_yearly' => 14999,
                'price_extra_branch' => 9999, 'price_perpetual' => 49999, 'price_amc' => 11999,
                'branch_limit' => 1, 'trial_days' => 14,
                'modules' => null, // all
                'features' => $allFeaturesOff,
            ],
            [
                // Internal only — the super admin assigns this to the first 25 hospitals.
                'code' => 'founder', 'name' => 'Founder', 'sort_order' => 3, 'is_public' => false,
                'description' => 'Hospital plan for early adopters — 2-year price lock, free setup & migration',
                'price_monthly' => 0, 'price_half_yearly' => 0, 'price_yearly' => 11999,
                'price_extra_branch' => 9999, 'price_perpetual' => null, 'price_amc' => null,
                'branch_limit' => 1, 'trial_days' => 14,
                'modules' => null,
                'features' => $allFeaturesOff,
            ],
            [
                'code' => 'enterprise', 'name' => 'Enterprise', 'sort_order' => 4, 'is_public' => false,
                'description' => 'Chains & groups — custom limits, priority support, custom domain',
                'price_monthly' => 0, 'price_half_yearly' => 0, 'price_yearly' => 0,
                'price_extra_branch' => 8000, 'price_perpetual' => null, 'price_amc' => null,
                'branch_limit' => 50, 'trial_days' => 0,
                'modules' => null,
                'features' => ['ai' => true, 'custom_domain' => true, 'sms' => true, 'priority_support' => true],
            ],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['code' => $p['code']], $p);
        }

        // Retire the old "Hospital Multi" tier — branches are now an add-on on the Hospital plan.
        Plan::where('code', 'hospital_multi')->update(['is_public' => false, 'is_active' => false]);

        // Put the demo hospital on the "Hospital" plan with 2 branches, yearly, active.
        $hospital = Hospital::where('code', 'DEMO')->first();
        if ($hospital && ! $hospital->subscription) {
            app(SubscriptionService::class)->subscribe(
                $hospital,
                Plan::where('code', 'hospital')->first(),
                ['billing_cycle' => 'yearly', 'branches' => 2, 'trial' => false],
            );
        }

        $this->command->info('Plans seeded ('.count($plans).' + legacy retired).');
    }
}
