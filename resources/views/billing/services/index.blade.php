<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Services &amp; tariffs</h2>
            @can('billing.create')
                <a href="{{ route('billing.services.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ New service</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Category</label>
                    <select name="category" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($categories as $val => $label)
                            <option value="{{ $val }}" @selected(request('category') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Service</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Department</th>
                            <th class="px-4 py-3 font-medium text-right">Price</th>
                            <th class="px-4 py-3 font-medium text-right">GST</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($services as $s)
                            <tr class="{{ $s->is_active ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $s->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $s->code }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $categories[$s->category] ?? $s->category }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $s->department?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($s->price, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ rtrim(rtrim(number_format($s->gst_rate, 2), '0'), '.') }}%</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @can('billing.create')
                                        <a href="{{ route('billing.services.edit', $s) }}" class="text-teal-600 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('billing.services.destroy', $s) }}" class="inline" onsubmit="return confirm('Remove service?')">
                                            @csrf @method('DELETE')
                                            <button class="ml-2 text-red-500 hover:underline">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No services yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
