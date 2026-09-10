<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Drug Categories</h2>
    </x-slot>

    <div class="py-8" x-data="{ editing: null }">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <div class="flex gap-4 text-sm">
                <a href="{{ route('pharmacy.suppliers.index') }}" class="text-gray-500 hover:underline">Suppliers</a>
                <a href="{{ route('pharmacy.manufacturers.index') }}" class="text-gray-500 hover:underline">Manufacturers</a>
                <span class="font-medium text-teal-700">Categories</span>
            </div>

            <form method="POST" action="{{ route('pharmacy.categories.store') }}" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-end gap-3">
                @csrf
                <div><label class="block text-xs text-gray-500 mb-1">Name *</label><input name="name" required class="border-gray-300 rounded-md text-sm w-48"></div>
                <div class="flex-1 min-w-[200px]"><label class="block text-xs text-gray-500 mb-1">Description</label><input name="description" class="border-gray-300 rounded-md text-sm w-full"></div>
                <x-primary-button>Add</x-primary-button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr><th class="px-4 py-2 font-medium">Name</th><th class="px-4 py-2 font-medium">Description</th><th class="px-4 py-2 font-medium">Medicines</th><th></th></tr>
                    </thead>
                    @forelse ($categories as $c)
                        <tbody class="border-t border-gray-100">
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $c->name }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $c->description ?: '—' }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $c->medicines_count }}</td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" @click="editing = editing === {{ $c->id }} ? null : {{ $c->id }}" class="text-teal-600 hover:underline">Edit</button>
                                </td>
                            </tr>
                            <tr x-show="editing === {{ $c->id }}" x-cloak>
                                <td colspan="4" class="px-4 py-3 bg-gray-50">
                                    <form method="POST" action="{{ route('pharmacy.categories.update', $c) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')
                                        <input name="name" value="{{ $c->name }}" class="border-gray-300 rounded-md text-sm w-44">
                                        <input name="description" value="{{ $c->description }}" class="border-gray-300 rounded-md text-sm w-64" placeholder="description">
                                        <label class="inline-flex items-center gap-1 text-xs"><input type="checkbox" name="is_active" value="1" class="rounded text-teal-600" @checked($c->is_active)> Active</label>
                                        <button class="rounded bg-teal-600 text-white text-sm px-3 py-1.5">Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('pharmacy.categories.destroy', $c) }}" class="mt-2" onsubmit="return confirm('Remove?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 text-xs hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody><tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No categories yet.</td></tr></tbody>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
