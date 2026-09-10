<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Collections</h2>
            <button onclick="window.print()" class="text-sm text-gray-600 hover:underline">Print</button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end print:hidden">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From</label>
                    <input type="date" name="from" value="{{ $from->toDateString() }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To</label>
                    <input type="date" name="to" value="{{ $to->toDateString() }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Mode</label>
                    <select name="mode" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach (\App\Models\Payment::MODES as $m)
                            <option value="{{ $m }}" @selected(request('mode') === $m)>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Show</button>
            </form>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-2xl font-semibold text-green-600">₹{{ number_format($collected, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Collected</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-2xl font-semibold text-purple-600">₹{{ number_format($refunded, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Refunded</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($net, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Net</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-4">
                    <div class="text-2xl font-semibold text-gray-800">{{ $payments->count() }}</div>
                    <div class="text-xs text-gray-500 mt-1">Transactions</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="text-xs text-gray-500 mb-2">By mode</div>
                <div class="flex flex-wrap gap-4 text-sm">
                    @forelse ($byMode as $mode => $amount)
                        <span class="rounded bg-gray-100 px-3 py-1">{{ ucfirst(str_replace('_',' ',$mode)) }}: <span class="font-medium">₹{{ number_format($amount, 2) }}</span></span>
                    @empty
                        <span class="text-gray-400">No collections in range.</span>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">Receipt</th>
                            <th class="px-4 py-2 font-medium">Invoice</th>
                            <th class="px-4 py-2 font-medium">Patient</th>
                            <th class="px-4 py-2 font-medium">Date</th>
                            <th class="px-4 py-2 font-medium">Mode</th>
                            <th class="px-4 py-2 font-medium">By</th>
                            <th class="px-4 py-2 font-medium text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($payments as $p)
                            <tr>
                                <td class="px-4 py-2 font-mono text-gray-600">
                                    <a href="{{ route('billing.payments.receipt', $p) }}" class="hover:underline">{{ $p->payment_no }}</a>
                                </td>
                                <td class="px-4 py-2 font-mono text-gray-500">
                                    <a href="{{ route('billing.invoices.show', $p->invoice) }}" class="hover:underline">{{ $p->invoice->invoice_no }}</a>
                                </td>
                                <td class="px-4 py-2">{{ $p->patient->full_name }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $p->payment_date->format('d M Y') }}</td>
                                <td class="px-4 py-2 capitalize">{{ str_replace('_',' ',$p->mode) }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $p->receiver?->name }}</td>
                                <td class="px-4 py-2 text-right {{ $p->type === 'refund' ? 'text-purple-600' : '' }}">
                                    {{ $p->type === 'refund' ? '− ' : '' }}₹{{ number_format($p->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No transactions in range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
