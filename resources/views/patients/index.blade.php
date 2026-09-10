<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">{{ __("Patients") }}</h2>
            @can('patients.create')
                <a href="{{ route('patients.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ {{ __("Register patient") }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __("Search") }}</label>
                    <input name="q" value="{{ request('q') }}" placeholder="{{ __("UHID, name or phone") }}" class="border-gray-300 rounded-md shadow-sm text-sm w-64" autofocus>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ __("Status") }}</label>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">{{ __("All") }}</option>
                        <option value="active" @selected(request('status')==='active')>{{ __("Active") }}</option>
                        <option value="inactive" @selected(request('status')==='inactive')>{{ __("Inactive") }}</option>
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">{{ __("Search") }}</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __("UHID") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Name") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Age / Sex") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Phone") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("City") }}</th>
                            <th class="px-4 py-3 font-medium">{{ __("Registered") }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($patients as $p)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('patients.show', $p) }}'">
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $p->uhid }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $p->full_name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->age ?? '—' }}{{ $p->gender ? ' / '.ucfirst($p->gender[0]) : '' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->city ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-400">{{ $p->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">{{ __("No patients found.") }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $patients->links() }}
        </div>
    </div>
</x-app-layout>
