<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Wards &amp; Beds</h2>
            <a href="{{ route('wards.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ New ward</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Ward</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Floor</th>
                            <th class="px-4 py-3 font-medium">Daily rate</th>
                            <th class="px-4 py-3 font-medium">Beds</th>
                            <th class="px-4 py-3 font-medium">Occupancy</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($wards as $ward)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $ward->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $ward->code }}{{ $ward->gender_restriction !== 'any' ? ' · '.ucfirst($ward->gender_restriction).' only' : '' }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $ward->type_label }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $ward->floor ?: '—' }}</td>
                                <td class="px-4 py-3">₹{{ number_format($ward->default_daily_charge, 0) }}</td>
                                <td class="px-4 py-3">{{ $ward->beds_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-green-600">{{ $ward->available_beds_count }} free</span> /
                                    <span class="text-rose-600">{{ $ward->occupied_beds_count }} occ.</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('wards.beds', $ward) }}" class="text-teal-600 hover:underline">Beds</a>
                                    <a href="{{ route('wards.edit', $ward) }}" class="ml-3 text-gray-500 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No wards yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
