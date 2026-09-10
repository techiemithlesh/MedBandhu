<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\MedicineBatch;
use App\Support\PharmacyStock;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PharmacyStockController extends Controller
{
    public function index(Request $request, Tenancy $tenancy): View
    {
        $branchId = $tenancy->branchId();

        $query = MedicineBatch::withoutTenantScope()
            ->where('branch_id', $branchId)
            ->with('medicine.category')
            ->when($request->filled('q'), fn ($q) => $q->whereHas('medicine', fn ($m) => $m->search($request->string('q'))))
            ->when($request->input('filter') === 'expired', fn ($q) => $q->whereDate('expiry_date', '<', today())->where('quantity_available', '>', 0))
            ->when($request->input('filter') === 'expiring', fn ($q) => $q->expiringWithin(90))
            ->when($request->input('filter') === 'in_stock', fn ($q) => $q->where('quantity_available', '>', 0));

        $batches = $query->orderBy('expiry_date')->paginate(30)->withQueryString();

        return view('pharmacy.stock.index', [
            'batches' => $batches,
            'summary' => [
                'stock_value' => MedicineBatch::withoutTenantScope()->where('branch_id', $branchId)
                    ->where('quantity_available', '>', 0)
                    ->selectRaw('COALESCE(SUM(quantity_available * purchase_price), 0) v')->value('v'),
                'expiring' => MedicineBatch::withoutTenantScope()->where('branch_id', $branchId)->expiringWithin(90)->count(),
                'expired' => MedicineBatch::withoutTenantScope()->where('branch_id', $branchId)
                    ->whereDate('expiry_date', '<', today())->where('quantity_available', '>', 0)->count(),
            ],
        ]);
    }

    public function adjust(Request $request, MedicineBatch $batch, PharmacyStock $stock): RedirectResponse
    {
        $data = $request->validate([
            'delta' => ['required', 'integer', 'not_in:0'],
            'type' => ['required', Rule::in(['adjustment', 'expiry_writeoff'])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $stock->adjust($batch, $data['delta'], $data['type'], $data['note'] ?? null);

        return back()->with('status', 'Stock adjusted.');
    }
}
