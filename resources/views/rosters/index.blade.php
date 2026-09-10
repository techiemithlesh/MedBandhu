<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Duty Roster</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="GET" class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Month</label>
                    <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="border-gray-300 rounded-md shadow-sm text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Branch</label>
                    <select name="branch" class="border-gray-300 rounded-md shadow-sm text-sm">
                        @foreach ($branches as $id => $name)
                            <option value="{{ $id }}" @selected($branchId == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2">Show</button>
            </form>

            @can('rosters.manage')
            <form method="POST" action="{{ route('rosters.store') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month->format('Y-m') }}">
                <input type="hidden" name="branch_id" value="{{ $branchId }}">

                <div class="bg-white rounded-lg shadow-sm overflow-x-auto">
                    <table class="text-xs border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500">
                                <th class="sticky left-0 bg-gray-50 px-3 py-2 text-left font-medium min-w-[180px]">Staff</th>
                                @foreach ($days as $day)
                                    @php $dow = $month->setDay($day)->dayOfWeek; @endphp
                                    <th class="px-1 py-2 font-medium {{ in_array($dow, [0,6]) ? 'text-red-400' : '' }}">{{ $day }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($staff as $member)
                                <tr class="border-t border-gray-100">
                                    <td class="sticky left-0 bg-white px-3 py-1.5 whitespace-nowrap">
                                        <div class="font-medium text-gray-700">{{ $member->full_name }}</div>
                                        <div class="text-gray-400">{{ $member->type_label }}</div>
                                    </td>
                                    @foreach ($days as $day)
                                        @php $current = $entries->get($member->id.'|'.$day)?->first()?->shift; @endphp
                                        <td class="px-0.5 py-1">
                                            <select name="roster[{{ $member->id }}][{{ $day }}]"
                                                    class="border-gray-200 rounded text-[11px] py-1 px-1 w-14
                                                    {{ $current ? 'bg-teal-50' : '' }}">
                                                <option value="">–</option>
                                                @php $codes = ['morning'=>'M','evening'=>'E','night'=>'N','general'=>'Gen','off'=>'O']; @endphp
                                                @foreach ($shifts as $val => $label)
                                                    <option value="{{ $val }}" @selected($current === $val)>{{ $codes[$val] ?? $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($days) + 1 }}" class="px-3 py-10 text-center text-gray-400">No active staff in this branch.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex items-center gap-4">
                    <x-primary-button>Save roster</x-primary-button>
                    <span class="text-xs text-gray-400">M = morning, E = evening, N = night, Gen = general, O = off</span>
                </div>
            </form>
            @else
                <div class="bg-white rounded-lg shadow-sm overflow-x-auto p-4 text-sm text-gray-500">
                    You have view-only access to the roster.
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
