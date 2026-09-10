<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Pharmacy</h2>
            <div class="flex gap-3">
                @can('pharmacy.dispense')
                    <a href="{{ route('pharmacy.dispense.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">New sale</a>
                @endcan
                @can('pharmacy.purchase')
                    <a href="{{ route('pharmacy.purchases.create') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Receive stock</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('pharmacy.medicines.index') }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                    <div class="text-3xl font-semibold text-gray-800">{{ number_format($stats['medicines']) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Medicines</div>
                </a>
                <a href="{{ route('pharmacy.stock.index') }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                    <div class="text-3xl font-semibold text-gray-800">₹{{ number_format($stats['stock_value'], 0) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Stock value (cost)</div>
                </a>
                <a href="{{ route('pharmacy.stock.index', ['filter' => 'expiring']) }}" class="bg-white rounded-lg shadow-sm p-5 block hover:shadow-md transition">
                    <div class="text-3xl font-semibold {{ $stats['expiring'] ? 'text-amber-600' : 'text-gray-800' }}">{{ $stats['expiring'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Batches expiring &le;90d</div>
                </a>
                <div class="bg-white rounded-lg shadow-sm p-5">
                    <div class="text-3xl font-semibold text-gray-800">₹{{ number_format($stats['sales_today'], 0) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Sales today</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="font-medium text-gray-800 mb-3">Low stock (at / below reorder level)</h3>
                @forelse ($lowStock as $m)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0 text-sm">
                        <a href="{{ route('pharmacy.medicines.show', $m) }}" class="text-teal-600 hover:underline">{{ $m->display_name }}</a>
                        <span class="text-gray-500">{{ $m->stock }} in stock · reorder at {{ $m->reorder_level }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Nothing below reorder level.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
