<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Staff</h2>
            @can('staff.create')
                <a href="{{ route('staff.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ Add staff</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input name="q" value="{{ request('q') }}" placeholder="Name, code, phone"
                           class="border-gray-300 rounded-md shadow-sm text-sm w-52">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Type</label>
                    <select name="type" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Department</label>
                    <select name="department" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach ($departments as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('department') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All</option>
                        @foreach (['active','on_leave','suspended','resigned'] as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
                @if(request()->hasAny(['q','type','department','status']))
                    <a href="{{ route('staff.index') }}" class="text-sm text-gray-500 hover:underline">Reset</a>
                @endif
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Code</th>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Type</th>
                            <th class="px-4 py-3 font-medium">Department</th>
                            <th class="px-4 py-3 font-medium">Branch</th>
                            <th class="px-4 py-3 font-medium">Phone</th>
                            <th class="px-4 py-3 font-medium">Login</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($staff as $member)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('staff.show', $member) }}'">
                                <td class="px-4 py-3 text-gray-500">{{ $member->employee_code }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $member->full_name }}</td>
                                <td class="px-4 py-3">{{ $member->type_label }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $member->department?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $member->branch?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $member->phone ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($member->user_id)
                                        <span class="text-xs bg-teal-50 text-teal-700 rounded px-2 py-0.5">yes</span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5
                                        {{ $member->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst(str_replace('_',' ',$member->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No staff found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $staff->links() }}
        </div>
    </div>
</x-app-layout>
