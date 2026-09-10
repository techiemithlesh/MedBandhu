@props(['r'])

<div class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-end gap-3 print:hidden">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $r->from->toDateString() }}" class="border-gray-300 rounded-md shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $r->to->toDateString() }}" class="border-gray-300 rounded-md shadow-sm text-sm">
        </div>
        <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Apply</button>
    </form>
    <div class="flex flex-wrap gap-2 text-xs">
        @foreach (\App\Support\ReportRange::presets() as $name => $p)
            <a href="{{ request()->url() }}?from={{ $p['from'] }}&to={{ $p['to'] }}"
               class="rounded-full px-3 py-1 border border-gray-200 text-gray-600 hover:bg-gray-50">{{ $name }}</a>
        @endforeach
    </div>
    <div class="ml-auto flex items-center gap-3">
        <span class="text-xs text-gray-400">{{ $r->label() }} · {{ $r->days() }} day(s)</span>
        <button onclick="window.print()" class="text-sm text-gray-600 hover:underline">Print</button>
    </div>
</div>
