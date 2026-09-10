@php
    $tenancy = app(\App\Support\Tenancy::class);
    $activeHospital = $tenancy->hospital();
    $activeBranch = $tenancy->branch();
    $userBranches = auth()->user()->branches ?? collect();
@endphp

<nav class="bg-white border-b border-gray-200">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="text-sm text-gray-400 hidden sm:block">
                    {{ $activeHospital?->name ?? __('Platform administration') }}
                </span>
            </div>

            <div class="flex items-center gap-3">
                @if(auth()->user()->isSuperAdmin() && $activeHospital)
                    <form method="POST" action="{{ route('context.hospital.leave') }}">
                        @csrf
                        <button class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 text-xs font-medium px-3 py-1 hover:bg-amber-200">
                            {{ __('Working in') }} {{ $activeHospital->code }} <span class="text-amber-500">&times;</span>
                        </button>
                    </form>
                @endif

                @if($userBranches->count() > 1)
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3"/></svg>
                                {{ $activeBranch?->name ?? __('Select branch') }}
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @foreach($userBranches as $b)
                                <form method="POST" action="{{ route('context.branch') }}">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $b->id }}">
                                    <button type="submit" class="block w-full text-start px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $activeBranch?->id === $b->id ? 'font-semibold text-teal-700' : '' }}">
                                        {{ $b->name }}
                                    </button>
                                </form>
                            @endforeach
                        </x-slot>
                    </x-dropdown>
                @elseif($activeBranch)
                    <span class="text-sm text-gray-500 hidden sm:block">{{ $activeBranch->name }}</span>
                @endif

                <x-install-button variant="light" class="hidden md:block" />
                <x-lang-switcher variant="light" class="hidden sm:inline-flex" />

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 hover:text-gray-900">
                            <div>{{ Auth::user()->name }}</div>
                            <svg class="ms-1 fill-current h-4 w-4" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs text-gray-400 border-b">
                            {{ auth()->user()->getRoleNames()->implode(', ') ?: __('Super Admin') }}
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
