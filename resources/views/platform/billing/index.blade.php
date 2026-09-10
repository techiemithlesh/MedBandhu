<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Platform billing</h2>
            <a href="{{ route('platform.billing.invoices') }}" class="text-sm text-gray-600 hover:underline">All invoices</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-gray-800">₹{{ number_format($mrr, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">MRR (est.)</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold text-green-600">₹{{ number_format($collectedThisMonth, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Collected this month</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-2xl font-semibold {{ $outstanding > 0 ? 'text-rose-600' : 'text-gray-800' }}">₹{{ number_format($outstanding, 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Outstanding</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-700 mt-1">
                        <span class="font-semibold text-green-600">{{ $counts['active'] }}</span> active ·
                        <span class="font-semibold text-blue-600">{{ $counts['trialing'] }}</span> trial ·
                        <span class="font-semibold text-rose-600">{{ $counts['past_due'] }}</span> past due
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{{ $counts['perpetual'] }} perpetual licence(s)</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Hospital</th>
                            <th class="px-4 py-3 font-medium">Plan</th>
                            <th class="px-4 py-3 font-medium">Type / cycle</th>
                            <th class="px-4 py-3 font-medium text-right">Amount</th>
                            <th class="px-4 py-3 font-medium">Renews / valid</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($subscriptions as $s)
                            <tr>
                                <td class="px-4 py-3">
                                    <a href="{{ route('platform.hospitals.show', $s->hospital) }}" class="font-medium text-teal-700 hover:underline">{{ $s->hospital->name }}</a>
                                    <div class="text-xs text-gray-400">{{ $s->hospital->code }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $s->plan->name }}</td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ ucfirst($s->licence_type) }} · {{ ucfirst($s->billing_cycle) }} · {{ $s->branches }} br</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($s->amount, 0) }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ optional($s->periodEndsOn())->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5
                                        @class([
                                            'bg-green-100 text-green-700' => $s->status === 'active',
                                            'bg-blue-100 text-blue-700' => $s->status === 'trialing',
                                            'bg-rose-100 text-rose-700' => $s->status === 'past_due',
                                            'bg-gray-100 text-gray-500' => in_array($s->status, ['suspended','cancelled']),
                                        ])">{{ ucwords(str_replace('_',' ',$s->status)) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No subscriptions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
