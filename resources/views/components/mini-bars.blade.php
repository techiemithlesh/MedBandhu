@props(['data' => [], 'money' => false, 'height' => 120])

@php
    $data = collect($data);
    $max = max(1, $data->max() ?: 1);
    $count = $data->count();
    $barW = $count > 0 ? 100 / $count : 100;
@endphp

<div class="bg-white rounded-lg shadow-sm p-4">
    {{ $slot }}
    @if ($count === 0)
        <p class="text-sm text-gray-400 py-8 text-center">No data in range.</p>
    @else
        <div class="flex items-end gap-px" style="height: {{ $height }}px">
            @foreach ($data as $label => $value)
                <div class="flex-1 group relative flex flex-col justify-end items-center">
                    <div class="w-full bg-teal-500/80 hover:bg-teal-600 rounded-t"
                         style="height: {{ max(1, round($value / $max * 100)) }}%"></div>
                    <div class="absolute -top-6 hidden group-hover:block whitespace-nowrap text-[10px] bg-gray-800 text-white rounded px-1.5 py-0.5 z-10">
                        {{ \Illuminate\Support\Str::of($label)->afterLast('-') }}: {{ $money ? '₹'.number_format($value) : $value }}
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-[10px] text-gray-400 mt-1">
            <span>{{ \Illuminate\Support\Carbon::parse($data->keys()->first())->format('d M') }}</span>
            <span>{{ \Illuminate\Support\Carbon::parse($data->keys()->last())->format('d M') }}</span>
        </div>
    @endif
</div>
