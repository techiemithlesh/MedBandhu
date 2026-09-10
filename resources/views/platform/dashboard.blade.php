<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Platform Overview</h2>
            <a href="{{ route('platform.hospitals.create') }}" class="inline-flex items-center rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                + Add hospital
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('status'))
                <div class="rounded-md bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-3xl font-semibold text-gray-800">{{ $hospitals->count() }}</div>
                    <div class="text-sm text-gray-500 mt-1">Hospitals</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-3xl font-semibold text-gray-800">{{ $hospitals->sum('branches_count') }}</div>
                    <div class="text-sm text-gray-500 mt-1">Branches</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-3xl font-semibold text-gray-800">{{ $totalUsers }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total users</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">Hospital</th>
                            <th class="px-4 py-3 font-medium">Plan</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Branches</th>
                            <th class="px-4 py-3 font-medium">Users</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($hospitals as $h)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $h->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $h->code }}@if($h->city) &middot; {{ $h->city }}@endif</div>
                                </td>
                                <td class="px-4 py-3 capitalize">{{ $h->subscription_plan }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5 {{ $h->subscription_status === 'active' && $h->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $h->is_active ? $h->subscription_status : 'inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $h->branches_count }}</td>
                                <td class="px-4 py-3">{{ $h->users_count }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('platform.hospitals.show', $h) }}" class="text-teal-600 hover:underline">Manage</a>
                                    <form method="POST" action="{{ route('context.hospital.enter', $h) }}" class="inline">
                                        @csrf
                                        <button class="ml-3 text-gray-600 hover:underline">Enter &rarr;</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No hospitals yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
