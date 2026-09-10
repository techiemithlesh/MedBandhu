<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            New sale @if ($consultation) <span class="text-sm text-gray-400 font-normal">· Rx from {{ $consultation->doctor->full_name }}</span> @endif
        </h2>
    </x-slot>

    <div class="py-8" x-data="pos({
        presetLines: {{ Illuminate\Support\Js::from($lines) }},
        patient: {{ $patient ? Illuminate\Support\Js::from(['id' => $patient->id, 'uhid' => $patient->uhid, 'name' => $patient->full_name]) : 'null' }},
        consultationId: {{ $consultation?->id ?? 'null' }}
    })">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" action="{{ route('pharmacy.dispense.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="patient_id" :value="patient?.id">
                <input type="hidden" name="consultation_id" :value="consultationId">

                <div class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <x-input-label value="Patient / customer" />
                        <template x-if="patient">
                            <div class="mt-1 flex items-center justify-between rounded-md border border-teal-200 bg-teal-50 px-3 py-2 text-sm">
                                <span><span class="font-medium" x-text="patient.name"></span> · <span class="font-mono" x-text="patient.uhid"></span></span>
                                <button type="button" @click="patient = null; consultationId = null" class="text-teal-600 text-xs hover:underline">change</button>
                            </div>
                        </template>
                        <template x-if="!patient">
                            <div class="mt-1 relative">
                                <input type="text" x-model="pq" @input.debounce.300ms="plookup()" placeholder="Search patient, or leave blank for walk-in"
                                       class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <div x-show="presults.length" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg text-sm">
                                    <template x-for="r in presults" :key="r.id">
                                        <button type="button" @click="patient = r; presults = []; pq = ''" class="block w-full text-left px-3 py-2 hover:bg-gray-50">
                                            <span class="font-medium" x-text="r.name"></span><span class="text-gray-400" x-text="' · '+r.uhid"></span>
                                        </button>
                                    </template>
                                </div>
                                <input type="text" name="customer_name" x-model="customerName" placeholder="Walk-in name (optional)" class="mt-2 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>
                        </template>
                    </div>
                    <div>
                        <x-input-label for="payment_mode" value="Payment *" />
                        <select id="payment_mode" name="payment_mode" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach (['cash', 'card', 'upi', 'credit'] as $pm)
                                <option value="{{ $pm }}">{{ ucfirst($pm) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 space-y-3">
                    <h3 class="font-medium text-gray-800">Items</h3>
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="border border-gray-100 rounded-md p-3 space-y-2">
                            <div x-show="row.hint" class="text-xs text-amber-700">Prescribed: <span x-text="row.hint"></span></div>
                            <div class="grid grid-cols-12 gap-2 items-end">
                                <div class="col-span-5 relative">
                                    <label class="block text-xs text-gray-500">Medicine</label>
                                    <input type="text" x-model="row.query" @input.debounce.250ms="mlookup(i)" @focus="row.open = true"
                                           :placeholder="row.medicine_label || 'Search…'"
                                           class="block w-full border-gray-300 rounded text-sm">
                                    <input type="hidden" :name="`items[${i}][medicine_id]`" :value="row.medicine_id">
                                    <div x-show="row.open && row.results.length" class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg text-sm">
                                        <template x-for="m in row.results" :key="m.id">
                                            <button type="button" @click="pickMed(i, m)" class="block w-full text-left px-3 py-2 hover:bg-gray-50">
                                                <span class="font-medium" x-text="m.name"></span>
                                                <span class="text-xs" :class="m.stock > 0 ? 'text-green-600' : 'text-red-500'" x-text="' · '+m.stock+' in stock'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs text-gray-500">Qty</label>
                                    <input type="number" min="1" :name="`items[${i}][quantity]`" x-model.number="row.quantity" class="block w-full border-gray-300 rounded text-sm">
                                    <div class="text-[10px]" :class="row.stock !== null && row.quantity > row.stock ? 'text-red-500' : 'text-gray-400'"
                                         x-text="row.stock !== null ? row.stock+' available' : ''"></div>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs text-gray-500">Rate ₹ (FEFO)</label>
                                    <input type="text" :value="row.sale_price ? row.sale_price.toFixed(2) : '—'" readonly class="block w-full border-gray-200 bg-gray-50 rounded text-sm">
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-xs text-gray-500">Disc %</label>
                                    <input type="number" min="0" max="100" :name="`items[${i}][discount_percent]`" x-model.number="row.discount_percent" class="block w-full border-gray-300 rounded text-sm">
                                </div>
                                <div class="col-span-1 text-right">
                                    <button type="button" @click="rows.splice(i,1)" class="text-red-500 text-sm">×</button>
                                </div>
                            </div>
                            <div class="text-right text-xs text-gray-600">Line: ₹<span x-text="lineTotal(row).toFixed(2)"></span></div>
                        </div>
                    </template>
                    <button type="button" @click="addRow()" class="text-sm text-teal-600 hover:underline">+ Add item</button>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 flex justify-end">
                    <div class="text-right text-sm space-y-1">
                        <div>Subtotal: ₹<span x-text="subtotal().toFixed(2)"></span></div>
                        <div>Discount: − ₹<span x-text="discountTotal().toFixed(2)"></span></div>
                        <div class="text-lg font-semibold">Payable: ₹<span x-text="Math.round(subtotal() - discountTotal()).toFixed(2)"></span></div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="!rows.some(r => r.medicine_id && r.quantity > 0)"
                            class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-40">
                        Complete sale
                    </button>
                    <a href="{{ route('pharmacy.dispense.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function pos(cfg) {
            const blank = (hint = '') => ({ hint, query: '', results: [], open: false, medicine_id: '', medicine_label: '', quantity: 1, sale_price: 0, stock: null, discount_percent: 0 });
            return {
                patient: cfg.patient,
                consultationId: cfg.consultationId,
                customerName: '',
                pq: '', presults: [],
                rows: cfg.presetLines.length ? cfg.presetLines.map(l => blank(l.hint)) : [blank()],
                addRow() { this.rows.push(blank()); },
                plookup() {
                    if (this.pq.length < 2) { this.presults = []; return; }
                    fetch(`{{ route('patients.search') }}?q=${encodeURIComponent(this.pq)}`).then(r => r.json()).then(d => this.presults = d.patients);
                },
                mlookup(i) {
                    const q = this.rows[i].query;
                    if (q.length < 2) { this.rows[i].results = []; return; }
                    fetch(`{{ route('pharmacy.medicines.search') }}?q=${encodeURIComponent(q)}`).then(r => r.json()).then(d => { this.rows[i].results = d.medicines; this.rows[i].open = true; });
                },
                pickMed(i, m) {
                    const row = this.rows[i];
                    row.medicine_id = m.id; row.medicine_label = m.name; row.query = m.name;
                    row.results = []; row.open = false; row.stock = m.stock;
                    fetch(`/pharmacy/medicines/${m.id}/batches`).then(r => r.json()).then(d => {
                        row.sale_price = d.batches.length ? d.batches[0].sale_price : 0;
                    });
                },
                lineTotal(r) {
                    const gross = (r.quantity || 0) * (r.sale_price || 0);
                    return gross - gross * (r.discount_percent || 0) / 100;
                },
                subtotal() { return this.rows.reduce((s, r) => s + (r.quantity || 0) * (r.sale_price || 0), 0); },
                discountTotal() { return this.rows.reduce((s, r) => { const g = (r.quantity || 0) * (r.sale_price || 0); return s + g * (r.discount_percent || 0) / 100; }, 0); },
            }
        }
    </script>
</x-app-layout>
