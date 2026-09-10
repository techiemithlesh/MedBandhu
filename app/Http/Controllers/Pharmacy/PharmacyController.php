<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PharmacySale;
use App\Support\Tenancy;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function index(Tenancy $tenancy): View
    {
        $branchId = $tenancy->branchId();
        $today = CarbonImmutable::today();

        $lowStock = Medicine::where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->get()
            ->filter(function ($m) use ($branchId) {
                $onHand = MedicineBatch::withoutTenantScope()
                    ->where('branch_id', $branchId)->where('medicine_id', $m->id)
                    ->where('quantity_available', '>', 0)->sum('quantity_available');

                return $onHand <= $m->reorder_level;
            })
            ->take(20);

        return view('pharmacy.index', [
            'stats' => [
                'medicines' => Medicine::where('is_active', true)->count(),
                'stock_value' => MedicineBatch::withoutTenantScope()->where('branch_id', $branchId)
                    ->where('quantity_available', '>', 0)
                    ->selectRaw('COALESCE(SUM(quantity_available * purchase_price),0) v')->value('v'),
                'expiring' => MedicineBatch::withoutTenantScope()->where('branch_id', $branchId)->expiringWithin(90)->count(),
                'sales_today' => PharmacySale::completed()->where('branch_id', $branchId)
                    ->whereDate('sale_date', $today)->sum('total'),
            ],
            'lowStock' => $lowStock,
        ]);
    }
}
