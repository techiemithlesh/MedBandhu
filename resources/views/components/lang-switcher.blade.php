@props(['variant' => 'light'])

@php
    $locales = config('hms.locales', ['en' => 'English']);
    $current = app()->getLocale();
    $track = $variant === 'dark' ? 'bg-white/10' : 'bg-gray-100';
    $idle = $variant === 'dark' ? 'text-white/60 hover:text-white' : 'text-gray-500 hover:text-gray-800';
    $active = $variant === 'dark' ? 'bg-white text-slate-900' : 'bg-white text-teal-700 shadow-sm';
@endphp

<form method="POST" action="{{ route('locale.update') }}" {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full p-0.5 text-xs '.$track]) }}>
    @csrf
    <input type="hidden" name="_redirect" value="{{ url()->current() }}">
    @foreach ($locales as $code => $label)
        <button type="submit" name="locale" value="{{ $code }}"
                aria-pressed="{{ $current === $code ? 'true' : 'false' }}"
                class="rounded-full px-2.5 py-1 font-medium transition {{ $current === $code ? $active : $idle }}">
            {{ $code === 'en' ? 'EN' : $label }}
        </button>
    @endforeach
</form>
