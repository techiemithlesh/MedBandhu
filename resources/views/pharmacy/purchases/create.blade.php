<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Receive stock (GRN)</h2>
    </x-slot>

    <div class="py-8" x-data="grn({ medicines: {{ Illuminate\Support\Js::from($medicines) }} })">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            <form method="POST" action="{{ route('pharmacy.purchases.store') }}" class="space-y-4">
                @csrf

                <div class="bg-white rounded-lg shadow-sm p-6 grid sm:grid-cols-4 gap-4">
                    <div>
                        <x-input-label for="supplier_id" value="Supplier *" />
                        <select id="supplier_id" name="supplier_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">— Select —</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="invoice_number" value="Invoice no." />
                        <x-text-input id="invoice_number" name="invoice_number" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="invoice_date" value="Invoice date" />
                        <x-text-input id="invoice_date" name="invoice_date" type="date" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="received_date" value="Received date *" />
                        <x-text-input id="received_date" name="received_date" type="date" class="mt-1 block w-full" :value="now()->toDateString()" required />
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 space-y-3">
                    <h3 class="font-medium text-gray-800">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead class="text-left text-gray-500">
                                <tr>
                                    <th class="py-1 pr-2 font-medium">Medicine</th>
                                    <th class="py-1 pr-2 font-medium">Batch</th>
                                    <th class="py-1 pr-2 font-medium">Expiry</th>
                                    <th class="py-1 pr-2 font-medium">Qty</th>
                                    <th class="py-1 pr-2 font-medium">Free</th>
                                    <th class="py-1 pr-2 font-medium">Cost/unit</th>
                                    <th class="py-1 pr-2 font-medium">MRP</th>
                                    <th class="py-1 pr-2 font-medium">Sale ₹</th>
                                    <th class="py-1 pr-2 font-medium">Disc%</th>
                                    <th class="py-1 pr-2 font-medium">GST%</th>
                                    <th class="py-1 pr-2 font-medium text-right">Line ₹</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, i) in rows" :key="i">
                                    <tr class="border-t border-gray-100">
                                        <td class="py-1 pr-2">
                                            <select :name="`items[${i}][medicine_id]`" x-model.number="row.medicine_id" @change="onPick(i)" class="border-gray-300 rounded text-xs w-40" required>
                                                <option value="">—</option>
                                                <template x-for="m in medicines" :key="m.id">
                                                    <option :value="m.id" x-text="m.label"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-1 pr-2"><input :name="`items[${i}][batch_number]`" x-model="row.batch_number" class="border-gray-300 rounded text-xs w-20" required></td>
                                        <td class="py-1 pr-2"><input type="date" :name="`items[${i}][expiry_date]`" x-model="row.expiry_date" class="border-gray-300 rounded text-xs" required></td>
                                        <td class="py-1 pr-2"><input type="number" min="1" :name="`items[${i}][quantity]`" x-model.number="row.quantity" class="border-gray-300 rounded text-xs w-16" required></td>
                                        <td class="py-1 pr-2"><input type="number" min="0" :name="`items[${i}][free_quantity]`" x-model.number="row.free_quantity" class="border-gray-300 rounded text-xs w-14"></td>
                                        <td class="py-1 pr-2"><input type="number" step="0.01" min="0" :name="`items[${i}][purchase_price]`" x-model.number="row.purchase_price" class="border-gray-300 rounded text-xs w-20" required></td>
                                        <td class="py-1 pr-2"><input type="number" step="0.01" min="0" :name="`items[${i}][mrp]`" x-model.number="row.mrp" class="border-gray-300 rounded text-xs w-20" required></td>
                                        <td class="py-1 pr-2"><input type="number" step="0.01" min="0" :name="`items[${i}][sale_price]`" x-model.number="row.sale_price" class="border-gray-300 rounded text-xs w-20" required></td>
                                        <td class="py-1 pr-2"><input type="number" step="0.01" min="0" max="100" :name="`items[${i}][discount_percent]`" x-model.number="row.discount_percent" class="border-gray-300 rounded text-xs w-14"></td>
                                        <td class="py-1 pr-2"><input type="number" step="0.01" min="0" max="28" :name="`items[${i}][gst_rate]`" x-model.number="row.gst_rate" class="border-gray-300 rounded text-xs w-14" required></td>
                                        <td class="py-1 pr-2 text-right font-medium" x-text="lineTotal(row).toFixed(2)"></td>
                                        <td class="py-1"><button type="button" @click="rows.splice(i,1)" class="text-red-500">×</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" @click="addRow()" class="text-sm text-teal-600 hover:underline">+ Add item</button>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 flex flex-wrap justify-between items-end gap-4">
                    <div>
                        <x-input-label for="notes" value="Notes" />
                        <x-text-input id="notes" name="notes" class="mt-1 block w-64" />
                    </div>
                    <div class="text-sm space-y-1 text-right">
                        <div>Taxable: ₹<span x-text="taxable().toFixed(2)"></span></div>
                        <div>GST: ₹<span x-text="gst().toFixed(2)"></span></div>
                        <div class="flex items-center gap-2 justify-end">Discount ₹
                            <input type="number" step="0.01" min="0" name="discount" x-model.number="discount" class="w-24 border-gray-300 rounded text-sm">
                        </div>
                        <div class="text-lg font-semibold">Total: ₹<span x-text="grandTotal().toFixed(2)"></span></div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="rows.length === 0" class="rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 disabled:opacity-40">
                        Receive &amp; update stock
                    </button>
                    <a href="{{ route('pharmacy.purchases.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function grn(cfg) {
            const blank = () => ({ medicine_id: '', batch_number: '', expiry_date: '', quantity: 1, free_quantity: 0, purchase_price: 0, mrp: 0, sale_price: 0, discount_percent: 0, gst_rate: 12 });
            return {
                medicines: cfg.medicines,
                rows: [blank()],
                discount: 0,
                addRow() { this.rows.push(blank()); },
                onPick(i) {
                    const m = this.medicines.find(x => x.id === this.rows[i].medicine_id);
                    if (m) this.rows[i].gst_rate = m.gst;
                },
                lineTaxable(r) {
                    const gross = (r.quantity || 0) * (r.purchase_price || 0);
                    return gross - gross * (r.discount_percent || 0) / 100;
                },
                lineTotal(r) { return this.lineTaxable(r) * (1 + (r.gst_rate || 0) / 100); },
                taxable() { return this.rows.reduce((s, r) => s + this.lineTaxable(r), 0); },
                gst() { return this.rows.reduce((s, r) => s + this.lineTaxable(r) * (r.gst_rate || 0) / 100, 0); },
                grandTotal() { return this.taxable() + this.gst() - (this.discount || 0); },
            }
        }
    </script>
</x-app-layout>
