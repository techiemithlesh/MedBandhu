<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">{{ $invoice->invoice_no }}
                    <span class="text-sm text-gray-400">· {{ strtoupper($invoice->type) }}</span>
                </h2>
                <p class="text-sm text-gray-500">
                    <a href="{{ route('patients.show', $invoice->patient) }}" class="hover:underline">{{ $invoice->patient->full_name }}</a>
                    · {{ $invoice->patient->uhid }} · {{ $invoice->invoice_date->format('d M Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs rounded-full px-3 py-1
                    @class([
                        'bg-gray-100 text-gray-600' => in_array($invoice->status, ['draft','cancelled']),
                        'bg-blue-100 text-blue-700' => $invoice->status === 'finalized',
                        'bg-amber-100 text-amber-700' => $invoice->status === 'partially_paid',
                        'bg-green-100 text-green-700' => $invoice->status === 'paid',
                        'bg-purple-100 text-purple-700' => $invoice->status === 'refunded',
                    ])">{{ ucwords(str_replace('_',' ',$invoice->status)) }}</span>
                <button onclick="window.print()" class="text-sm text-gray-600 hover:underline">Print</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            @if ($invoice->appointment)
                <div class="text-sm text-gray-500">OPD visit with {{ $invoice->appointment->doctor->full_name }} on {{ $invoice->appointment->scheduled_date->format('d M Y') }}</div>
            @elseif ($invoice->admission)
                <div class="text-sm text-gray-500">IPD admission {{ $invoice->admission->admission_no }} · {{ $invoice->admission->days_admitted }} day(s)</div>
            @endif

            {{-- Items --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-500">
                        <tr>
                            <th class="px-4 py-2 font-medium">Description</th>
                            <th class="px-4 py-2 font-medium text-right">Qty</th>
                            <th class="px-4 py-2 font-medium text-right">Rate</th>
                            <th class="px-4 py-2 font-medium text-right">Disc%</th>
                            <th class="px-4 py-2 font-medium text-right">Amount</th>
                            @if ($invoice->isEditable()) <th></th> @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($invoice->items as $item)
                            <tr>
                                <td class="px-4 py-2">
                                    {{ $item->description }}
                                    <span class="text-xs text-gray-400">· {{ ucfirst($item->item_type) }}</span>
                                </td>
                                <td class="px-4 py-2 text-right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-2 text-right">{{ $item->discount_percent > 0 ? rtrim(rtrim(number_format($item->discount_percent, 2), '0'), '.').'%' : '—' }}</td>
                                <td class="px-4 py-2 text-right">₹{{ number_format($item->line_total, 2) }}</td>
                                @if ($invoice->isEditable())
                                    <td class="px-4 py-2 text-right">
                                        <form method="POST" action="{{ route('billing.invoices.items.remove', [$invoice, $item->id]) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:underline text-xs">remove</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No line items yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($invoice->isEditable())
                    @can('billing.create')
                        <div class="border-t border-gray-100 p-4 bg-gray-50" x-data="{
                            services: {{ Illuminate\Support\Js::from($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'price' => (float)$s->price, 'gst' => (float)$s->gst_rate])) }},
                            sid: '', desc: '', qty: 1, price: 0, disc: 0, gst: 0,
                            pick() { const s = this.services.find(x => x.id == this.sid); if (s) { this.desc = s.name; this.price = s.price; this.gst = s.gst; } }
                        }">
                            <form method="POST" action="{{ route('billing.invoices.items.add', $invoice) }}" class="flex flex-wrap items-end gap-2 text-sm">
                                @csrf
                                <div>
                                    <label class="block text-xs text-gray-500">Service</label>
                                    <select name="service_id" x-model="sid" @change="pick()" class="border-gray-300 rounded text-sm w-44">
                                        <option value="">— custom —</option>
                                        <template x-for="s in services" :key="s.id"><option :value="s.id" x-text="s.name"></option></template>
                                    </select>
                                </div>
                                <div class="flex-1 min-w-[140px]">
                                    <label class="block text-xs text-gray-500">Description</label>
                                    <input name="description" x-model="desc" class="block w-full border-gray-300 rounded text-sm">
                                </div>
                                <div><label class="block text-xs text-gray-500">Qty</label>
                                    <input name="quantity" type="number" step="0.01" min="0.01" x-model="qty" class="w-16 border-gray-300 rounded text-sm"></div>
                                <div><label class="block text-xs text-gray-500">Rate</label>
                                    <input name="unit_price" type="number" step="0.01" min="0" x-model="price" class="w-24 border-gray-300 rounded text-sm"></div>
                                <div><label class="block text-xs text-gray-500">Disc%</label>
                                    <input name="discount_percent" type="number" step="0.01" min="0" max="100" x-model="disc" class="w-16 border-gray-300 rounded text-sm"></div>
                                <div><label class="block text-xs text-gray-500">GST%</label>
                                    <input name="gst_rate" type="number" step="0.01" min="0" max="28" x-model="gst" class="w-16 border-gray-300 rounded text-sm"></div>
                                <button class="rounded bg-teal-600 text-white px-3 py-1.5">Add</button>
                            </form>
                        </div>
                    @endcan
                @endif
            </div>

            {{-- Totals + discount --}}
            <div class="bg-white rounded-lg shadow-sm p-6 flex flex-col sm:flex-row justify-between gap-6">
                <div class="flex-1">
                    @if ($invoice->isEditable())
                        @can('billing.create')
                            <form method="POST" action="{{ route('billing.invoices.update', $invoice) }}" class="space-y-2 text-sm">
                                @csrf @method('PUT')
                                <div>
                                    <label class="block text-xs text-gray-500">Invoice discount (₹)</label>
                                    <input name="discount" type="number" step="0.01" min="0" value="{{ $invoice->discount }}" class="w-32 border-gray-300 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500">Notes</label>
                                    <input name="notes" value="{{ $invoice->notes }}" class="w-full border-gray-300 rounded text-sm">
                                </div>
                                <button class="rounded border border-gray-300 px-3 py-1.5 text-sm hover:bg-gray-50">Update</button>
                            </form>
                        @endcan
                    @else
                        @if ($invoice->notes)<p class="text-sm text-gray-500">{{ $invoice->notes }}</p>@endif
                    @endif
                </div>
                <div class="w-full sm:w-64 text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>₹{{ number_format($invoice->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Discount</span><span>− ₹{{ number_format($invoice->discount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">GST (incl.)</span><span>₹{{ number_format($invoice->tax, 2) }}</span></div>
                    @if ((float) $invoice->round_off !== 0.0)
                        <div class="flex justify-between"><span class="text-gray-500">Round off</span><span>{{ $invoice->round_off > 0 ? '+' : '' }}₹{{ number_format($invoice->round_off, 2) }}</span></div>
                    @endif
                    <div class="flex justify-between font-semibold border-t border-gray-200 pt-1"><span>Total</span><span>₹{{ number_format($invoice->total, 2) }}</span></div>
                    <div class="flex justify-between text-green-600"><span>Paid</span><span>₹{{ number_format($invoice->amount_paid, 2) }}</span></div>
                    <div class="flex justify-between font-medium {{ $invoice->balance > 0 ? 'text-rose-600' : 'text-gray-500' }}"><span>Balance</span><span>₹{{ number_format($invoice->balance, 2) }}</span></div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-3">
                @if ($invoice->status === 'draft')
                    @can('billing.create')
                        <form method="POST" action="{{ route('billing.invoices.finalize', $invoice) }}">
                            @csrf
                            <button class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Finalize invoice</button>
                        </form>
                    @endcan
                @endif
                @if (! in_array($invoice->status, ['cancelled']) && $invoice->amount_paid == 0)
                    @can('billing.create')
                        <form method="POST" action="{{ route('billing.invoices.cancel', $invoice) }}" onsubmit="return confirm('Cancel this invoice?')">
                            @csrf
                            <button class="rounded-md border border-red-300 text-red-600 px-4 py-2 text-sm hover:bg-red-50">Cancel invoice</button>
                        </form>
                    @endcan
                @endif
            </div>

            {{-- Payments --}}
            @if (! in_array($invoice->status, ['draft', 'cancelled']))
                <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                    <h3 class="font-medium text-gray-800">Payments</h3>

                    <table class="min-w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($invoice->payments as $p)
                                <tr>
                                    <td class="py-2 font-mono text-gray-500">{{ $p->payment_no }}</td>
                                    <td class="py-2">
                                        <span class="text-xs rounded px-1.5 py-0.5 {{ $p->type === 'refund' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700' }}">{{ ucfirst($p->type) }}</span>
                                        {{ ucfirst(str_replace('_',' ',$p->mode)) }}
                                    </td>
                                    <td class="py-2 text-gray-500">{{ $p->payment_date->format('d M Y') }} · {{ $p->receiver?->name }}</td>
                                    <td class="py-2 text-right {{ $p->type === 'refund' ? 'text-purple-600' : '' }}">
                                        {{ $p->type === 'refund' ? '− ' : '' }}₹{{ number_format($p->amount, 2) }}
                                    </td>
                                    <td class="py-2 text-right"><a href="{{ route('billing.payments.receipt', $p) }}" class="text-teal-600 hover:underline text-xs">receipt</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-3 text-center text-gray-400">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="flex flex-wrap gap-6 border-t border-gray-100 pt-4">
                        @if ($invoice->balance > 0)
                            @can('billing.collect-payment')
                                <form method="POST" action="{{ route('billing.payments.store', $invoice) }}" class="flex flex-wrap items-end gap-2 text-sm">
                                    @csrf
                                    <div><label class="block text-xs text-gray-500">Amount</label>
                                        <input name="amount" type="number" step="0.01" min="0.01" max="{{ $invoice->balance }}" value="{{ $invoice->balance }}" required class="w-28 border-gray-300 rounded text-sm"></div>
                                    <div><label class="block text-xs text-gray-500">Mode</label>
                                        <select name="mode" class="border-gray-300 rounded text-sm">
                                            @foreach (\App\Models\Payment::MODES as $m)<option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>@endforeach
                                        </select></div>
                                    <div><label class="block text-xs text-gray-500">Reference</label>
                                        <input name="reference" class="w-28 border-gray-300 rounded text-sm"></div>
                                    <button class="rounded bg-teal-600 text-white px-3 py-1.5">Collect payment</button>
                                </form>
                            @endcan
                        @endif

                        @if ($invoice->amount_paid > 0)
                            @can('billing.refund')
                                <form method="POST" action="{{ route('billing.payments.refund', $invoice) }}" class="flex flex-wrap items-end gap-2 text-sm" onsubmit="return confirm('Record this refund?')">
                                    @csrf
                                    <div><label class="block text-xs text-gray-500">Refund ₹</label>
                                        <input name="amount" type="number" step="0.01" min="0.01" max="{{ $invoice->amount_paid }}" required class="w-24 border-gray-300 rounded text-sm"></div>
                                    <div><label class="block text-xs text-gray-500">Mode</label>
                                        <select name="mode" class="border-gray-300 rounded text-sm">
                                            @foreach (\App\Models\Payment::MODES as $m)<option value="{{ $m }}">{{ ucfirst(str_replace('_',' ',$m)) }}</option>@endforeach
                                        </select></div>
                                    <div><label class="block text-xs text-gray-500">Reason</label>
                                        <input name="notes" class="w-32 border-gray-300 rounded text-sm"></div>
                                    <button class="rounded border border-purple-300 text-purple-700 px-3 py-1.5 hover:bg-purple-50">Refund</button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
