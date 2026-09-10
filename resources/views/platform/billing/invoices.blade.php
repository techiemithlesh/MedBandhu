<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Platform invoices</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach (['sent','paid','void'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <input type="checkbox" name="overdue" value="1" class="rounded text-teal-600" @checked(request('overdue'))> Overdue only
                </label>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Invoice</th>
                            <th class="px-4 py-3 font-medium">Hospital</th>
                            <th class="px-4 py-3 font-medium">Due</th>
                            <th class="px-4 py-3 font-medium text-right">Total</th>
                            <th class="px-4 py-3 font-medium text-right">Balance</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('platform.billing.invoice', $inv) }}'">
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $inv->number }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $inv->hospital->name }}</td>
                                <td class="px-4 py-3 {{ $inv->isOverdue() ? 'text-rose-600 font-medium' : 'text-gray-500' }}">{{ $inv->due_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($inv->total, 2) }}</td>
                                <td class="px-4 py-3 text-right {{ $inv->balance > 0 ? 'text-rose-600' : 'text-gray-400' }}">₹{{ number_format($inv->balance, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5
                                        @class([
                                            'bg-green-100 text-green-700' => $inv->status === 'paid',
                                            'bg-amber-100 text-amber-700' => $inv->status === 'sent',
                                            'bg-gray-100 text-gray-500' => in_array($inv->status, ['draft','void']),
                                        ])">{{ ucfirst($inv->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No invoices.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $invoices->links() }}
        </div>
    </div>
</x-app-layout>
