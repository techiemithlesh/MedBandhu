<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">IPD Admissions</h2>
            @can('ipd.admit')
                <a href="{{ route('ipd.admissions.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Admit patient</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                        @foreach (['admitted' => 'Currently admitted', 'discharged' => 'Discharged', 'lama' => 'LAMA', 'expired' => 'Expired', 'all' => 'All'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('status', 'admitted') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Ward</label>
                    <select name="ward" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($wards as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('ward') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Admission</th>
                            <th class="px-4 py-3 font-medium">Patient</th>
                            <th class="px-4 py-3 font-medium">Ward / Bed</th>
                            <th class="px-4 py-3 font-medium">Doctor</th>
                            <th class="px-4 py-3 font-medium">Admitted</th>
                            <th class="px-4 py-3 font-medium">Days</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($admissions as $adm)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('ipd.admissions.show', $adm) }}'">
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $adm->admission_no }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $adm->patient->full_name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $adm->patient->uhid }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $adm->bed?->ward?->name ?? '—' }}{{ $adm->bed ? ' / '.$adm->bed->bed_number : '' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $adm->admittingDoctor?->full_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $adm->admitted_at->format('d M, H:i') }}</td>
                                <td class="px-4 py-3">{{ $adm->days_admitted }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5
                                        @class([
                                            'bg-green-100 text-green-700' => $adm->status === 'admitted',
                                            'bg-gray-100 text-gray-500' => $adm->status !== 'admitted',
                                        ])">{{ ucfirst($adm->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No admissions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $admissions->links() }}
        </div>
    </div>
</x-app-layout>
