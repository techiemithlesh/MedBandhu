<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Medicines</h2>
            @can('pharmacy.manage-stock')
                <a href="{{ route('pharmacy.medicines.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Add medicine</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input name="q" value="{{ request('q') }}" placeholder="Brand or generic" class="border-gray-300 rounded-md shadow-sm text-sm w-56">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Category</label>
                    <select name="category" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Generic</th>
                            <th class="px-4 py-3 font-medium">Form</th>
                            <th class="px-4 py-3 font-medium">GST</th>
                            <th class="px-4 py-3 font-medium">Sch.</th>
                            <th class="px-4 py-3 font-medium">In stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($medicines as $m)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('pharmacy.medicines.show', $m) }}'">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $m->display_name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $m->generic_name ?: '—' }}</td>
                                <td class="px-4 py-3 capitalize">{{ $m->form }}</td>
                                <td class="px-4 py-3">{{ rtrim(rtrim(number_format($m->gst_rate, 2), '0'), '.') }}%</td>
                                <td class="px-4 py-3">{{ $m->schedule === 'none' ? '—' : $m->schedule }}</td>
                                <td class="px-4 py-3">
                                    <span class="{{ $m->stock <= $m->reorder_level && $m->reorder_level > 0 ? 'text-amber-600 font-medium' : 'text-gray-600' }}">{{ $m->stock }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No medicines. <a href="{{ route('pharmacy.medicines.create') }}" class="text-teal-600 hover:underline">Add one</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $medicines->links() }}
        </div>
    </div>
</x-app-layout>
