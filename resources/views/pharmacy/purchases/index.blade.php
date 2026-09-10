<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Purchases (GRN)</h2>
            <a href="{{ route('pharmacy.purchases.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Receive stock</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Supplier</label>
                    <select name="supplier" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('supplier') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">GRN</th>
                            <th class="px-4 py-3 font-medium">Supplier</th>
                            <th class="px-4 py-3 font-medium">Invoice</th>
                            <th class="px-4 py-3 font-medium">Received</th>
                            <th class="px-4 py-3 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($purchases as $p)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('pharmacy.purchases.show', $p) }}'">
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $p->purchase_no }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $p->supplier->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->invoice_number ?: '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->received_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($p->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No purchases yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $purchases->links() }}
        </div>
    </div>
</x-app-layout>
