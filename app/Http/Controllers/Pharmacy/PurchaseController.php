<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\PharmacyStock;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(protected PharmacyStock $stock) {}

    public function index(Request $request): View
    {
        return view('pharmacy.purchases.index', [
            'purchases' => Purchase::with('supplier')
                ->where('branch_id', app(Tenancy::class)->branchId())
                ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_id', $request->integer('supplier')))
                ->latest('received_date')->latest('id')
                ->paginate(20)->withQueryString(),
            'suppliers' => Supplier::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('pharmacy.purchases.create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'medicines' => Medicine::where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'strength', 'gst_rate'])
                ->map(fn ($m) => ['id' => $m->id, 'label' => $m->display_name, 'gst' => (float) $m->gst_rate]),
        ]);
    }

    public function store(Request $request, Tenancy $tenancy): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')],
            'invoice_number' => ['nullable', 'string', 'max:60'],
            'invoice_date' => ['nullable', 'date'],
            'received_date' => ['required', 'date', 'before_or_equal:today'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', Rule::exists('medicines', 'id')],
            'items.*.batch_number' => ['required', 'string', 'max:40'],
            'items.*.expiry_date' => ['required', 'date', 'after:today'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.free_quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'items.*.mrp' => ['required', 'numeric', 'min:0'],
            'items.*.sale_price' => ['required', 'numeric', 'min:0'],
            'items.*.gst_rate' => ['required', 'numeric', 'min:0', 'max:28'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $purchase = DB::transaction(function () use ($data, $tenancy) {
            $purchase = Purchase::create([
                'branch_id' => $tenancy->branchId(),
                'supplier_id' => $data['supplier_id'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? null,
                'received_date' => $data['received_date'],
                'discount' => $data['discount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            $tax = 0;

            foreach ($data['items'] as $row) {
                $qty = (int) $row['quantity'];
                $gross = $qty * (float) $row['purchase_price'];
                $lineDiscount = $gross * ((float) ($row['discount_percent'] ?? 0) / 100);
                $taxable = $gross - $lineDiscount;
                $lineGst = $taxable * ((float) $row['gst_rate'] / 100);
                $lineTotal = round($taxable + $lineGst, 2);

                $item = $purchase->items()->create([
                    'medicine_id' => $row['medicine_id'],
                    'batch_number' => $row['batch_number'],
                    'expiry_date' => $row['expiry_date'],
                    'quantity' => $qty,
                    'free_quantity' => (int) ($row['free_quantity'] ?? 0),
                    'purchase_price' => $row['purchase_price'],
                    'mrp' => $row['mrp'],
                    'sale_price' => $row['sale_price'],
                    'discount_percent' => $row['discount_percent'] ?? 0,
                    'gst_rate' => $row['gst_rate'],
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $taxable;
                $tax += $lineGst;

                $this->stock->receive($item->load('purchase'), $purchase->branch_id);
            }

            $purchase->update([
                'subtotal' => round($subtotal, 2),
                'tax' => round($tax, 2),
                'total' => round($subtotal + $tax - (float) $purchase->discount, 2),
            ]);

            return $purchase;
        });

        return redirect()->route('pharmacy.purchases.show', $purchase)
            ->with('status', "Purchase {$purchase->purchase_no} received — stock updated.");
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'items.medicine']);

        return view('pharmacy.purchases.show', compact('purchase'));
    }
}
