<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Doctors</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input name="q" value="{{ request('q') }}" placeholder="Name" class="border-gray-300 rounded-md shadow-sm text-sm w-52">
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
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Filter</button>
            </form>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($doctors as $doc)
                    <a href="{{ route('doctors.show', $doc) }}" class="bg-white rounded-lg shadow-sm p-5 hover:shadow-md transition block">
                        <div class="flex items-center gap-3">
                            @if ($doc->photo_path)
                                <img src="{{ Storage::url($doc->photo_path) }}" class="h-12 w-12 rounded-full object-cover">
                            @else
                                <div class="h-12 w-12 rounded-full bg-teal-50 text-teal-700 flex items-center justify-center font-semibold">
                                    {{ Str::of($doc->first_name)->substr(0,1) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="font-medium text-gray-800 truncate">{{ $doc->full_name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $doc->doctorProfile?->specialization ?? $doc->department?->name ?? 'General' }}</div>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-gray-500 flex justify-between">
                            <span>{{ $doc->doctorProfile?->qualifications ?? '—' }}</span>
                            <span>₹{{ number_format($doc->doctorProfile?->consultation_fee ?? 0) }}</span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 col-span-full text-center py-10">No doctors yet. Add staff with type "Doctor".</p>
                @endforelse
            </div>

            {{ $doctors->links() }}
        </div>
    </div>
</x-app-layout>
