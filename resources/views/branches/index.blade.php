<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Branches</h2>
            @if ($hospital->canAddBranch())
                <a href="{{ route('branches.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Add branch</a>
            @else
                <a href="{{ route('billing.subscription.show') }}" class="rounded-md border border-amber-300 text-amber-700 px-4 py-2 text-sm hover:bg-amber-50">Upgrade to add branches</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm p-4 text-sm text-gray-500">
                Using <span class="font-medium text-gray-800">{{ $branches->count() }}</span> of
                <span class="font-medium text-gray-800">{{ $hospital->branch_limit }}</span> branch(es) allowed on your plan.
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Branch</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Location</th>
                            <th class="px-4 py-3 font-medium text-right">Staff</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($branches as $b)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $b->name }}</div>
                                    <div class="text-xs text-gray-400">{{ $b->code }}</div>
                                </td>
                                <td class="px-4 py-3 capitalize">{{ $b->type }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ collect([$b->city, $b->state])->filter()->implode(', ') ?: '—' }}</td>
                                <td class="px-4 py-3 text-right">{{ $b->users_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5 {{ $b->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $b->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('branches.edit', $b) }}" class="text-teal-600 hover:underline">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
