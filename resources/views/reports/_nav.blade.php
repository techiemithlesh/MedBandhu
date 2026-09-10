@php $q = ['from' => $r->from->toDateString(), 'to' => $r->to->toDateString()]; @endphp
<div class="flex flex-wrap gap-4 text-sm border-b border-gray-200 pb-2 print:hidden">
    <a href="{{ route('reports.index', $q) }}" class="{{ request()->routeIs('reports.index') ? 'font-medium text-teal-700 border-b-2 border-teal-600 pb-2 -mb-2' : 'text-gray-500 hover:text-gray-700' }}">Overview</a>
    <a href="{{ route('reports.opd', $q) }}" class="{{ request()->routeIs('reports.opd') ? 'font-medium text-teal-700 border-b-2 border-teal-600 pb-2 -mb-2' : 'text-gray-500 hover:text-gray-700' }}">OPD</a>
    <a href="{{ route('reports.ipd', $q) }}" class="{{ request()->routeIs('reports.ipd') ? 'font-medium text-teal-700 border-b-2 border-teal-600 pb-2 -mb-2' : 'text-gray-500 hover:text-gray-700' }}">IPD &amp; occupancy</a>
    @can('reports.view-financial')
        <a href="{{ route('reports.revenue', $q) }}" class="{{ request()->routeIs('reports.revenue') ? 'font-medium text-teal-700 border-b-2 border-teal-600 pb-2 -mb-2' : 'text-gray-500 hover:text-gray-700' }}">Revenue</a>
        <a href="{{ route('reports.pharmacy', $q) }}" class="{{ request()->routeIs('reports.pharmacy') ? 'font-medium text-teal-700 border-b-2 border-teal-600 pb-2 -mb-2' : 'text-gray-500 hover:text-gray-700' }}">Pharmacy</a>
    @endcan
    <a href="{{ route('reports.patients', $q) }}" class="{{ request()->routeIs('reports.patients') ? 'font-medium text-teal-700 border-b-2 border-teal-600 pb-2 -mb-2' : 'text-gray-500 hover:text-gray-700' }}">Patients</a>
</div>
