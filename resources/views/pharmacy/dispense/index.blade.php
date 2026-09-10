<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Dispense</h2>
            <a href="{{ route('pharmacy.dispense.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ New sale</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-medium text-gray-800 mb-3">Prescriptions waiting to be dispensed</h3>
                @forelse ($pending as $c)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0 text-sm">
                        <div>
                            <span class="font-medium text-gray-800">{{ $c->patient->full_name }}</span>
                            <span class="text-gray-400">· {{ $c->patient->uhid }} · {{ $c->doctor->full_name }} · {{ $c->created_at->format('d M') }}</span>
                            <div class="text-xs text-gray-500">{{ $c->items->pluck('drug_name')->take(4)->implode(', ') }}{{ $c->items->count() > 4 ? '…' : '' }}</div>
                        </div>
                        <a href="{{ route('pharmacy.dispense.create', ['consultation' => $c->id]) }}" class="rounded-md bg-teal-50 text-teal-700 text-xs font-medium px-3 py-1.5 hover:bg-teal-100">Dispense</a>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No pending prescriptions.</p>
                @endforelse
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <div class="px-4 py-3 font-medium text-gray-700">Recent sales</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">Bill</th>
                            <th class="px-4 py-2 font-medium">Customer</th>
                            <th class="px-4 py-2 font-medium">Date</th>
                            <th class="px-4 py-2 font-medium">Payment</th>
                            <th class="px-4 py-2 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($sales as $s)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('pharmacy.dispense.show', $s) }}'">
                                <td class="px-4 py-2 font-mono text-gray-600">{{ $s->sale_no }}</td>
                                <td class="px-4 py-2">{{ $s->customer_label }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $s->sale_date->format('d M Y') }}</td>
                                <td class="px-4 py-2 capitalize">{{ $s->payment_mode }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($s->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No sales yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sales->links() }}
        </div>
    </div>
</x-app-layout>
