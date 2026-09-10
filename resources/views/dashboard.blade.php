<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Dashboard') }}</h2>
            <span class="text-sm text-gray-500">
                {{ $hospital?->name }}@if($branch) &middot; {{ $branch->name }}@endif
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('status'))
                <div class="rounded-md bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-3xl font-semibold text-gray-800">{{ $stats['staff'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Active staff') }}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-3xl font-semibold text-gray-800">{{ number_format($stats['patients']) }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Patients') }}</div>
                </div>
                <a href="{{ route('opd.index') }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                    <div class="text-3xl font-semibold text-gray-800">{{ $stats['appts_today'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Appts today') }}</div>
                </a>
                @can('ipd.view')
                    <a href="{{ route('ipd.admissions.index') }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                        <div class="text-3xl font-semibold text-rose-600">{{ $stats['inpatients'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ __('Inpatients') }}</div>
                    </a>
                    <a href="{{ route('ipd.board') }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                        <div class="text-3xl font-semibold text-green-600">{{ $stats['beds_free'] }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ __('Beds free') }}</div>
                    </a>
                @endcan
                @can('billing.view')
                    <a href="{{ route('billing.invoices.index', ['status' => 'partially_paid']) }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                        <div class="text-2xl font-semibold {{ $stats['outstanding'] > 0 ? 'text-rose-600' : 'text-gray-800' }}">₹{{ number_format($stats['outstanding'], 0) }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ __('Outstanding') }}</div>
                    </a>
                    <a href="{{ route('billing.payments.index') }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                        <div class="text-2xl font-semibold text-green-600">₹{{ number_format($stats['collected_today'], 0) }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ __('Collected today') }}</div>
                    </a>
                @endcan
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-3xl font-semibold text-gray-800">{{ $stats['branches'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Branches') }}</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-3">{{ __('Your access') }}</h3>
                <p class="text-sm text-gray-500 mb-4">
                    {{ __('Role:') }} <span class="font-medium text-gray-700">{{ auth()->user()->getRoleNames()->implode(', ') ?: '—' }}</span>
                </p>
                <div class="flex flex-wrap gap-2">
                    @forelse(auth()->user()->getAllPermissions()->pluck('name')->sort() as $perm)
                        <span class="text-xs bg-gray-100 text-gray-600 rounded px-2 py-0.5">{{ $perm }}</span>
                    @empty
                        <span class="text-sm text-gray-400">{{ __('No specific permissions.') }}</span>
                    @endforelse
                </div>
            </div>

            @if ($hospital)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">{{ __('Modules') }}</h3>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                        @php
                            $moduleLabels = [
                                'patients' => __('Patients'),
                                'appointments' => __('Appointments'),
                                'opd' => __('OPD Queue'),
                                'ipd' => __('IPD & Beds'),
                                'beds' => __('Wards & Beds'),
                                'pharmacy' => __('Pharmacy'),
                                'billing' => __('Billing'),
                                'reports' => __('Reports'),
                            ];
                        @endphp
                        @foreach($moduleLabels as $key => $label)
                            @php $on = $hospital->moduleEnabled($key); @endphp
                            <div class="flex items-center justify-between rounded border border-gray-100 px-3 py-2">
                                <span class="text-gray-700">{{ $label }}</span>
                                <span class="text-xs {{ $on ? 'text-green-600' : 'text-gray-300' }}">
                                    {{ $on ? __('On') : __('Off') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
