@php
    $tenancy = app(\App\Support\Tenancy::class);
    $hospital = $tenancy->hospital();
    $isSuper = auth()->user()->isSuperAdmin();
    $link = 'flex items-center gap-3 px-4 py-2 text-sm rounded-md mx-2';
    $active = 'bg-slate-900 text-white font-medium';
    $idle = 'text-slate-300 hover:bg-slate-700/60 hover:text-white';
@endphp

<div class="h-16 flex items-center gap-2 px-4 border-b border-slate-700">
    <span class="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-teal-500 text-white font-bold">{{ substr(config('app.name', 'M'), 0, 1) }}</span>
    <div class="min-w-0">
        <div class="text-sm font-semibold text-white truncate">{{ $hospital?->name ?? config('app.name').' '.__('Platform') }}</div>
        <div class="text-[11px] text-slate-400 truncate">{{ $hospital?->code ?? __('super admin') }}</div>
    </div>
</div>

<nav class="py-4 space-y-1">
    <a href="{{ route('dashboard') }}" class="{{ $link }} {{ request()->routeIs('dashboard') ? $active : $idle }}">
        {{ __('Dashboard') }}
    </a>

    @if($isSuper && ! $hospital)
        <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Platform') }}</div>
        <a href="{{ route('platform.hospitals.index') }}" class="{{ $link }} {{ request()->routeIs('platform.hospitals.*') ? $active : $idle }}">{{ __('Hospitals') }}</a>
        <a href="{{ route('platform.plans.index') }}" class="{{ $link }} {{ request()->routeIs('platform.plans.*') ? $active : $idle }}">{{ __('Plans') }}</a>
        <a href="{{ route('platform.billing.index') }}" class="{{ $link }} {{ request()->routeIs('platform.billing.*') ? $active : $idle }}">{{ __('Billing & MRR') }}</a>
    @endif

    @if($hospital)
        <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Staff & Doctors') }}</div>

        @can('departments.view')
            <a href="{{ route('departments.index') }}" class="{{ $link }} {{ request()->routeIs('departments.*') ? $active : $idle }}">{{ __('Departments') }}</a>
        @endcan
        @can('staff.view')
            <a href="{{ route('staff.index') }}" class="{{ $link }} {{ request()->routeIs('staff.*') ? $active : $idle }}">{{ __('Staff') }}</a>
        @endcan
        @can('doctors.view')
            <a href="{{ route('doctors.index') }}" class="{{ $link }} {{ request()->routeIs('doctors.*') ? $active : $idle }}">{{ __('Doctors') }}</a>
        @endcan
        @can('rosters.view')
            <a href="{{ route('rosters.index') }}" class="{{ $link }} {{ request()->routeIs('rosters.*') ? $active : $idle }}">{{ __('Duty Roster') }}</a>
        @endcan

        <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Clinical') }}</div>

        @can('patients.view')
            <a href="{{ route('patients.index') }}" class="{{ $link }} {{ request()->routeIs('patients.*') ? $active : $idle }}">{{ __('Patients') }}</a>
        @endcan
        @can('appointments.view')
            <a href="{{ route('appointments.index') }}" class="{{ $link }} {{ request()->routeIs('appointments.*') ? $active : $idle }}">{{ __('Appointments') }}</a>
        @endcan
        @can('opd.view')
            <a href="{{ route('opd.index') }}" class="{{ $link }} {{ request()->routeIs('opd.*') || request()->routeIs('consultations.*') ? $active : $idle }}">{{ __('OPD Queue') }}</a>
        @endcan

        @can('ipd.view')
            <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('IPD & Beds') }}</div>
            <a href="{{ route('ipd.board') }}" class="{{ $link }} {{ request()->routeIs('ipd.board') ? $active : $idle }}">{{ __('Bed Board') }}</a>
            <a href="{{ route('ipd.admissions.index') }}" class="{{ $link }} {{ request()->routeIs('ipd.admissions.*') ? $active : $idle }}">{{ __('Admissions') }}</a>
            @can('beds.manage')
                <a href="{{ route('wards.index') }}" class="{{ $link }} {{ request()->routeIs('wards.*') || request()->routeIs('beds.*') ? $active : $idle }}">{{ __('Wards & Beds') }}</a>
            @endcan
        @endcan

        @can('pharmacy.view')
            @module('pharmacy')
                <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Pharmacy') }}</div>
                <a href="{{ route('pharmacy.index') }}" class="{{ $link }} {{ request()->routeIs('pharmacy.index') ? $active : $idle }}">{{ __('Overview') }}</a>
                @can('pharmacy.dispense')
                    <a href="{{ route('pharmacy.dispense.index') }}" class="{{ $link }} {{ request()->routeIs('pharmacy.dispense.*') ? $active : $idle }}">{{ __('Dispense') }}</a>
                @endcan
                <a href="{{ route('pharmacy.stock.index') }}" class="{{ $link }} {{ request()->routeIs('pharmacy.stock.*') ? $active : $idle }}">{{ __('Stock') }}</a>
                @can('pharmacy.purchase')
                    <a href="{{ route('pharmacy.purchases.index') }}" class="{{ $link }} {{ request()->routeIs('pharmacy.purchases.*') ? $active : $idle }}">{{ __('Purchases') }}</a>
                @endcan
                <a href="{{ route('pharmacy.medicines.index') }}" class="{{ $link }} {{ request()->routeIs('pharmacy.medicines.*') ? $active : $idle }}">{{ __('Medicines') }}</a>
                @can('pharmacy.manage-stock')
                    <a href="{{ route('pharmacy.suppliers.index') }}" class="{{ $link }} {{ request()->routeIs('pharmacy.suppliers.*') || request()->routeIs('pharmacy.manufacturers.*') || request()->routeIs('pharmacy.categories.*') ? $active : $idle }}">{{ __('Suppliers & masters') }}</a>
                @endcan
            @endmodule
        @endcan

        @can('billing.view')
            <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Billing') }}</div>
            <a href="{{ route('billing.invoices.index') }}" class="{{ $link }} {{ request()->routeIs('billing.invoices.*') ? $active : $idle }}">{{ __('Invoices') }}</a>
            <a href="{{ route('billing.payments.index') }}" class="{{ $link }} {{ request()->routeIs('billing.payments.*') ? $active : $idle }}">{{ __('Collections') }}</a>
            <a href="{{ route('billing.services.index') }}" class="{{ $link }} {{ request()->routeIs('billing.services.*') ? $active : $idle }}">{{ __('Services') }}</a>
        @endcan

        @can('reports.view')
            <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Reports') }}</div>
            <a href="{{ route('reports.index') }}" class="{{ $link }} {{ request()->routeIs('reports.index') ? $active : $idle }}">{{ __('Overview') }}</a>
            <a href="{{ route('reports.opd') }}" class="{{ $link }} {{ request()->routeIs('reports.opd') ? $active : $idle }}">{{ __('OPD') }}</a>
            <a href="{{ route('reports.ipd') }}" class="{{ $link }} {{ request()->routeIs('reports.ipd') ? $active : $idle }}">{{ __('IPD & occupancy') }}</a>
            @can('reports.view-financial')
                <a href="{{ route('reports.revenue') }}" class="{{ $link }} {{ request()->routeIs('reports.revenue') ? $active : $idle }}">{{ __('Revenue') }}</a>
                <a href="{{ route('reports.pharmacy') }}" class="{{ $link }} {{ request()->routeIs('reports.pharmacy') ? $active : $idle }}">{{ __('Pharmacy') }}</a>
            @endcan
            <a href="{{ route('reports.patients') }}" class="{{ $link }} {{ request()->routeIs('reports.patients') ? $active : $idle }}">{{ __('Patients') }}</a>
        @endcan

        @canany(['branches.manage', 'subscription.view'])
            <div class="px-4 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Account') }}</div>
            @can('branches.manage')
                <a href="{{ route('branches.index') }}" class="{{ $link }} {{ request()->routeIs('branches.*') ? $active : $idle }}">{{ __('Branches') }}</a>
            @endcan
            @can('subscription.view')
                <a href="{{ route('billing.subscription.show') }}" class="{{ $link }} {{ request()->routeIs('billing.subscription.*') ? $active : $idle }}">
                    {{ __('Subscription') }}
                    @if($hospital->access_status !== 'active')
                        <span class="ml-auto text-[10px] px-1.5 rounded bg-amber-500 text-white">!</span>
                    @endif
                </a>
            @endcan
        @endcanany
    @endif
</nav>
