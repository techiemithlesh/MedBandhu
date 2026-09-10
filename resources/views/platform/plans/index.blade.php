<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Plans</h2>
            <a href="{{ route('platform.plans.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ New plan</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Plan</th>
                            <th class="px-4 py-3 font-medium text-right">Monthly</th>
                            <th class="px-4 py-3 font-medium text-right">Yearly</th>
                            <th class="px-4 py-3 font-medium text-right">Extra branch</th>
                            <th class="px-4 py-3 font-medium text-right">Branches</th>
                            <th class="px-4 py-3 font-medium">Modules</th>
                            <th class="px-4 py-3 font-medium text-right">Subs</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($plans as $p)
                            <tr class="{{ $p->is_active ? '' : 'opacity-50' }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $p->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $p->code }}{{ $p->is_public ? '' : ' · private' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($p->price_monthly, 0) }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($p->price_yearly, 0) }}</td>
                                <td class="px-4 py-3 text-right">₹{{ number_format($p->price_extra_branch, 0) }}</td>
                                <td class="px-4 py-3 text-right">{{ $p->branch_limit }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $p->modules ? count($p->modules).' of '.count(config('hms.modules')) : 'All' }}</td>
                                <td class="px-4 py-3 text-right">{{ $p->subscriptions_count }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('platform.plans.edit', $p) }}" class="text-teal-600 hover:underline">Edit</a>
                                    @unless ($p->subscriptions_count)
                                        <form method="POST" action="{{ route('platform.plans.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete plan?')">
                                            @csrf @method('DELETE')
                                            <button class="ml-2 text-red-500 hover:underline">Delete</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No plans yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
