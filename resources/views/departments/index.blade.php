<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800">Departments</h2>
            @can('departments.manage')
                <a href="{{ route('departments.create') }}" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">+ New department</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Code</th>
                            <th class="px-4 py-3 font-medium">Head</th>
                            <th class="px-4 py-3 font-medium">Staff</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($departments as $dept)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $dept->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $dept->code }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $dept->head?->full_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $dept->staff_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-xs rounded-full px-2 py-0.5 {{ $dept->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $dept->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @can('departments.manage')
                                        <a href="{{ route('departments.edit', $dept) }}" class="text-teal-600 hover:underline">Edit</a>
                                        <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete this department?')">
                                            @csrf @method('DELETE')
                                            <button class="ml-3 text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No departments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
