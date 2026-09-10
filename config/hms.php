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

    // Public contact details — shown on the marketing site / landing page.
    'contact' => [
        'phone' => env('MEDBANDHU_PHONE', '+91 90000 00000'),
        'whatsapp' => env('MEDBANDHU_WHATSAPP', '919000000000'),
        'email' => env('MEDBANDHU_EMAIL', 'hello@medbandhu.com'),
        'address' => env('MEDBANDHU_ADDRESS', 'Patna, Bihar, India'),
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
