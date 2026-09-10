@props(['variant' => 'light'])

@php
    $classes = $variant === 'dark'
        ? 'border border-white/40 text-white hover:bg-white/10'
        : 'border border-teal-300 text-teal-700 hover:bg-teal-50';
@endphp

<div x-data="pwaInstall" x-show="available" x-cloak {{ $attributes->only('class') }}>
    <button type="button" @click="install"
            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold {{ $classes }}">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/>
        </svg>
        {{ __('Install app') }}
    </button>
</div>

@once
<script>
    (function () {
        let deferred = null;

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferred = e;
            window.dispatchEvent(new CustomEvent('pwa-installable'));
        });
        window.addEventListener('appinstalled', function () {
            deferred = null;
            window.dispatchEvent(new CustomEvent('pwa-installed'));
        });

        document.addEventListener('alpine:init', function () {
            Alpine.data('pwaInstall', function () {
                return {
                    available: !!deferred,
                    init() {
                        window.addEventListener('pwa-installable', () => { this.available = true; });
                        window.addEventListener('pwa-installed', () => { this.available = false; });
                    },
                    async install() {
                        if (!deferred) return;
                        deferred.prompt();
                        await deferred.userChoice;
                        deferred = null;
                        this.available = false;
                    },
                };
            });
        });
    })();
</script>
@endonce
