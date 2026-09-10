<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Manufacturers</h2>
    </x-slot>

    <div class="py-8" x-data="{ editing: null }">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />
            <div class="flex gap-4 text-sm">
                <a href="{{ route('pharmacy.suppliers.index') }}" class="text-gray-500 hover:underline">Suppliers</a>
                <span class="font-medium text-teal-700">Manufacturers</span>
                <a href="{{ route('pharmacy.categories.index') }}" class="text-gray-500 hover:underline">Categories</a>
            </div>

            <form method="POST" action="{{ route('pharmacy.manufacturers.store') }}" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-end gap-3">
                @csrf
                <div><label class="block text-xs text-gray-500 mb-1">Name *</label><input name="name" required class="border-gray-300 rounded-md text-sm w-48"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Phone</label><input name="phone" class="border-gray-300 rounded-md text-sm w-32"></div>
                <div><label class="block text-xs text-gray-500 mb-1">Email</label><input name="email" type="email" class="border-gray-300 rounded-md text-sm w-48"></div>
                <x-primary-button>Add</x-primary-button>
            </form>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr><th class="px-4 py-2 font-medium">Name</th><th class="px-4 py-2 font-medium">Contact</th><th class="px-4 py-2 font-medium">Medicines</th><th></th></tr>
                    </thead>
                    @forelse ($manufacturers as $m)
                        <tbody class="border-t border-gray-100">
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-800">{{ $m->name }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ collect([$m->phone, $m->email])->filter()->implode(' · ') ?: '—' }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $m->medicines_count }}</td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" @click="editing = editing === {{ $m->id }} ? null : {{ $m->id }}" class="text-teal-600 hover:underline">Edit</button>
                                </td>
                            </tr>
                            <tr x-show="editing === {{ $m->id }}" x-cloak>
                                <td colspan="4" class="px-4 py-3 bg-gray-50">
                                    <form method="POST" action="{{ route('pharmacy.manufacturers.update', $m) }}" class="flex flex-wrap items-end gap-3">
                                        @csrf @method('PUT')
                                        <input name="name" value="{{ $m->name }}" class="border-gray-300 rounded-md text-sm w-44">
                                        <input name="phone" value="{{ $m->phone }}" class="border-gray-300 rounded-md text-sm w-32" placeholder="phone">
                                        <input name="email" value="{{ $m->email }}" class="border-gray-300 rounded-md text-sm w-44" placeholder="email">
                                        <label class="inline-flex items-center gap-1 text-xs"><input type="checkbox" name="is_active" value="1" class="rounded text-teal-600" @checked($m->is_active)> Active</label>
                                        <button class="rounded bg-teal-600 text-white text-sm px-3 py-1.5">Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('pharmacy.manufacturers.destroy', $m) }}" class="mt-2" onsubmit="return confirm('Remove?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 text-xs hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody><tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No manufacturers yet.</td></tr></tbody>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
