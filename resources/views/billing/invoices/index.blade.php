<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Invoices</h2>
            @can('billing.create')
                <a href="{{ route('billing.invoices.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ New invoice</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input name="q" value="{{ request('q') }}" placeholder="Invoice no or patient" class="border-gray-300 rounded-md shadow-sm text-sm w-56">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach (['draft','finalized','partially_paid','paid','cancelled','refunded'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucwords(str_replace('_',' ',$st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Type</label>
                    <select name="type" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach (['opd','ipd','general'] as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ strtoupper($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Invoice</th>
                            <th class="px-4 py-3 font-medium">Patient</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium text-right">Total</th>
                            <th class="px-4 py-3 font-medium text-right">Balance</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('billing.invoices.show', $inv) }}'">
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $inv->invoice_no }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $inv->patient->full_name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $inv->patient->uhid }}</div>
                                </td>
                                <td class="px-4 py-3">{{ strtoupper($inv->type) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $inv->invoice_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($inv->total, 2) }}</td>
                                <td class="px-4 py-3 text-right {{ $inv->balance > 0 ? 'text-rose-600 font-medium' : 'text-gray-400' }}">₹{{ number_format($inv->balance, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5
                                        @class([
                                            'bg-gray-100 text-gray-500' => in_array($inv->status, ['draft','cancelled']),
                                            'bg-blue-100 text-blue-700' => $inv->status === 'finalized',
                                            'bg-amber-100 text-amber-700' => $inv->status === 'partially_paid',
                                            'bg-green-100 text-green-700' => $inv->status === 'paid',
                                            'bg-purple-100 text-purple-700' => $inv->status === 'refunded',
                                        ])">{{ ucwords(str_replace('_',' ',$inv->status)) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No invoices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $invoices->links() }}
        </div>
    </div>
</x-app-layout>
