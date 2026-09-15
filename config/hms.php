<?php

/**
 * Single source of truth for HMS permissions, roles and modules.
 * Consumed by RolesAndPermissionsSeeder and HospitalProvisioner.
 */
return [

    /*
     * spatie/laravel-permission runs in "teams" mode with team_id = hospital_id.
     * The pivot tables make team_id NOT NULL, so platform-level (Super Admin)
     * roles are stored under this sentinel team id instead of null.
     */
    'platform_team_id' => 0,

    // UI languages offered by the in-app switcher (code => native label).
    'locales' => [
        'en' => 'English',
        'hi' => 'हिन्दी',
    ],

    // One-click public demo. When enabled, GET /demo logs the visitor straight
    // into the demo hospital as the read-limited "Demo" role user below.
    'demo' => [
        'enabled' => env('DEMO_MODE', env('APP_ENV', 'production') !== 'production'),
        'email' => env('DEMO_EMAIL', 'demo@medbandhu.test'),
        'hospital_code' => 'DEMO',
    ],

    // Cities with a dedicated local-SEO landing page at
    // /hospital-management-software/{slug}. Add a row here to publish a new
    // city page + sitemap entry — nothing else to change.
    'cities' => [
        ['slug' => 'patna', 'name' => 'Patna', 'state' => 'Bihar'],
        ['slug' => 'gaya', 'name' => 'Gaya', 'state' => 'Bihar'],
        ['slug' => 'muzaffarpur', 'name' => 'Muzaffarpur', 'state' => 'Bihar'],
        ['slug' => 'bhagalpur', 'name' => 'Bhagalpur', 'state' => 'Bihar'],
        ['slug' => 'darbhanga', 'name' => 'Darbhanga', 'state' => 'Bihar'],
        ['slug' => 'purnia', 'name' => 'Purnia', 'state' => 'Bihar'],
        ['slug' => 'siwan', 'name' => 'Siwan', 'state' => 'Bihar'],
        ['slug' => 'gopalganj', 'name' => 'Gopalganj', 'state' => 'Bihar'],
        ['slug' => 'chhapra', 'name' => 'Chhapra', 'state' => 'Bihar'],
        ['slug' => 'motihari', 'name' => 'Motihari', 'state' => 'Bihar'],
        ['slug' => 'ranchi', 'name' => 'Ranchi', 'state' => 'Jharkhand'],
        ['slug' => 'jamshedpur', 'name' => 'Jamshedpur', 'state' => 'Jharkhand'],
        ['slug' => 'dhanbad', 'name' => 'Dhanbad', 'state' => 'Jharkhand'],
        ['slug' => 'bokaro', 'name' => 'Bokaro', 'state' => 'Jharkhand'],
        ['slug' => 'hazaribagh', 'name' => 'Hazaribagh', 'state' => 'Jharkhand'],
        ['slug' => 'deoghar', 'name' => 'Deoghar', 'state' => 'Jharkhand'],
        ['slug' => 'raipur', 'name' => 'Raipur', 'state' => 'Chhattisgarh'],
        ['slug' => 'bilaspur', 'name' => 'Bilaspur', 'state' => 'Chhattisgarh'],
        ['slug' => 'durg', 'name' => 'Durg', 'state' => 'Chhattisgarh'],
        ['slug' => 'korba', 'name' => 'Korba', 'state' => 'Chhattisgarh'],
        ['slug' => 'kolkata', 'name' => 'Kolkata', 'state' => 'West Bengal'],
        ['slug' => 'howrah', 'name' => 'Howrah', 'state' => 'West Bengal'],
        ['slug' => 'asansol', 'name' => 'Asansol', 'state' => 'West Bengal'],
        ['slug' => 'siliguri', 'name' => 'Siliguri', 'state' => 'West Bengal'],
    ],

    // Public contact details — shown on the marketing site / landing page.
    'contact' => [
        'phone' => env('MEDBANDHU_PHONE', '+91 90000 00000'),
        'whatsapp' => env('MEDBANDHU_WHATSAPP', '919000000000'),
        'email' => env('MEDBANDHU_EMAIL', 'hello@medbandhu.com'),
        'address' => env('MEDBANDHU_ADDRESS', 'Patna, Bihar, India'),
        // Every office shown in the footer, and matched by city slug for a
        // local-presence callout on that city's SEO page (see city.blade.php).
        'offices' => [
            ['city_slug' => 'ranchi', 'label' => 'Ranchi (HQ)', 'address' => env('MEDBANDHU_ADDRESS', 'Ranchi, Jharkhand, India')],
            ['city_slug' => 'kolkata', 'label' => 'Kolkata', 'address' => env('MEDBANDHU_ADDRESS_KOLKATA', 'VIP Enclave, VIP Road, Block F, 201, Kolkata 700059, West Bengal')],
        ],
    ],

    // Where hospitals send their subscription payment (manual / offline flow,
    // used until an online subscription gateway is live). Shown on the
    // hospital's "Subscription & billing" page next to each pending invoice.
    'pay_to' => [
        'upi' => env('MEDBANDHU_PAY_UPI'),                       // e.g. medbandhu@okhdfcbank
        'account_name' => env('MEDBANDHU_PAY_AC_NAME', 'MedBandhu'),
        'bank_name' => env('MEDBANDHU_PAY_BANK'),                // e.g. HDFC Bank, Ranchi
        'account_number' => env('MEDBANDHU_PAY_AC_NUMBER'),
        'ifsc' => env('MEDBANDHU_PAY_IFSC'),
    ],

    // Tenant-selectable feature modules (stored on hospitals.settings->modules).
    'modules' => [
        'patients', 'appointments', 'opd', 'ipd', 'beds',
        'pharmacy', 'billing', 'reports',
    ],

    // Every permission in the system, grouped only for readability.
    'permissions' => [
        // platform (Super Admin territory)
        'hospitals.view', 'hospitals.manage',

        // hospital administration
        'branches.view', 'branches.manage',
        'users.view', 'users.manage',
        'roles.view', 'roles.manage',
        'audit.view',
        'settings.manage',
        'subscription.view', 'subscription.pay',

        // HR
        'departments.view', 'departments.manage',
        'staff.view', 'staff.create', 'staff.update', 'staff.delete',
        'doctors.view', 'doctors.create', 'doctors.update', 'doctors.delete',
        'schedules.manage',
        'rosters.view', 'rosters.manage',

        // clinical — OPD
        'patients.view', 'patients.create', 'patients.update', 'patients.delete',
        'appointments.view', 'appointments.create', 'appointments.update', 'appointments.cancel',
        'opd.view', 'opd.manage-queue', 'opd.consult', 'vitals.record',

        // clinical — IPD / beds
        'ipd.view', 'ipd.admit', 'ipd.transfer', 'ipd.discharge',
        'beds.view', 'beds.manage',
        'nursing.notes',

        // pharmacy
        'pharmacy.view', 'pharmacy.dispense', 'pharmacy.manage-stock', 'pharmacy.purchase',

        // billing
        'billing.view', 'billing.create', 'billing.collect-payment', 'billing.refund',

        // reports
        'reports.view', 'reports.view-financial',
    ],

    /**
     * Role => permissions. Use ['*'] for "all permissions".
     * 'Super Admin' is created once at the platform level (no team);
     * every other role is created per-hospital by HospitalProvisioner.
     */
    'roles' => [
        'Super Admin' => ['*'],

        'Hospital Admin' => ['*'], // all, minus platform perms (filtered in provisioner)

        'Branch Admin' => [
            'branches.view', 'users.view', 'audit.view',
            'departments.view',
            'staff.view', 'staff.create', 'staff.update',
            'doctors.view', 'doctors.create', 'doctors.update',
            'schedules.manage', 'rosters.view', 'rosters.manage',
            'patients.view', 'patients.create', 'patients.update',
            'appointments.view', 'appointments.create', 'appointments.update', 'appointments.cancel',
            'opd.view', 'opd.manage-queue', 'vitals.record',
            'ipd.view', 'ipd.admit', 'ipd.transfer', 'ipd.discharge',
            'beds.view', 'beds.manage', 'nursing.notes',
            'pharmacy.view', 'pharmacy.dispense', 'pharmacy.manage-stock', 'pharmacy.purchase',
            'billing.view', 'billing.create', 'billing.collect-payment',
            'reports.view', 'reports.view-financial',
        ],

        'Doctor' => [
            'patients.view', 'patients.update',
            'appointments.view', 'appointments.update',
            'opd.view', 'opd.consult', 'vitals.record',
            'ipd.view', 'ipd.admit', 'ipd.transfer', 'ipd.discharge',
            'beds.view', 'staff.view', 'doctors.view', 'rosters.view',
        ],

        'Nurse' => [
            'patients.view',
            'opd.view', 'opd.manage-queue', 'vitals.record',
            'ipd.view', 'beds.view', 'nursing.notes',
            'appointments.view',
        ],

        'Receptionist' => [
            'patients.view', 'patients.create', 'patients.update',
            'appointments.view', 'appointments.create', 'appointments.update', 'appointments.cancel',
            'opd.view', 'opd.manage-queue', 'vitals.record',
            'beds.view',
            'billing.view', 'billing.create', 'billing.collect-payment',
        ],

        'Pharmacist' => [
            'patients.view',
            'pharmacy.view', 'pharmacy.dispense', 'pharmacy.manage-stock', 'pharmacy.purchase',
            'billing.view', 'billing.collect-payment',
        ],

        'Accountant' => [
            'billing.view', 'billing.create', 'billing.collect-payment', 'billing.refund',
            'reports.view', 'reports.view-financial',
        ],

        'Store Manager' => [
            'pharmacy.view', 'pharmacy.manage-stock', 'pharmacy.purchase',
        ],

        // Public "try it" account. Full clinical happy-path, but nothing
        // destructive (no *.delete / *.cancel / *.refund) and no account admin
        // (no users / roles / settings / branches / subscription / audit).
        'Demo' => [
            'departments.view', 'staff.view', 'doctors.view', 'schedules.manage',
            'rosters.view', 'rosters.manage',
            'patients.view', 'patients.create', 'patients.update',
            'appointments.view', 'appointments.create', 'appointments.update',
            'opd.view', 'opd.manage-queue', 'opd.consult', 'vitals.record',
            'ipd.view', 'ipd.admit', 'ipd.transfer', 'ipd.discharge',
            'beds.view', 'beds.manage', 'nursing.notes',
            'pharmacy.view', 'pharmacy.dispense', 'pharmacy.manage-stock', 'pharmacy.purchase',
            'billing.view', 'billing.create', 'billing.collect-payment',
            'reports.view', 'reports.view-financial',
        ],
    ],

    // Permissions that only make sense at the platform level; stripped from
    // every per-hospital role (including that hospital's "Hospital Admin").
    'platform_only_permissions' => [
        'hospitals.view', 'hospitals.manage',
    ],
];
