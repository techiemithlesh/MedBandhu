<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Suppliers</h2>
            <a href="{{ route('pharmacy.suppliers.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ New supplier</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <div class="flex gap-4 text-sm">
                <span class="font-medium text-teal-700">Suppliers</span>
                <a href="{{ route('pharmacy.manufacturers.index') }}" class="text-gray-500 hover:underline">Manufacturers</a>
                <a href="{{ route('pharmacy.categories.index') }}" class="text-gray-500 hover:underline">Categories</a>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Contact</th>
                            <th class="px-4 py-3 font-medium">GSTIN</th>
                            <th class="px-4 py-3 font-medium">Purchases</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($suppliers as $s)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $s->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $s->contact_person }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ collect([$s->phone, $s->email])->filter()->implode(' · ') ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $s->gstin ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $s->purchases_count }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('pharmacy.suppliers.edit', $s) }}" class="text-teal-600 hover:underline">Edit</a>
                                    @if (! $s->purchases_count)
                                        <form method="POST" action="{{ route('pharmacy.suppliers.destroy', $s) }}" class="inline" onsubmit="return confirm('Remove?')">
                                            @csrf @method('DELETE')
                                            <button class="ml-2 text-red-500 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No suppliers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
