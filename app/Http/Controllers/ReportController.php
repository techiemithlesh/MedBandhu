<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Invoice;
use App\Models\IpdAdmission;
use App\Models\IpdBedMovement;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PharmacySale;
use App\Models\Purchase;
use App\Support\ReportRange;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected Tenancy $tenancy) {}

    public function index(Request $request): View
    {
        $r = ReportRange::fromRequest($request);
        $branchId = $this->tenancy->branchId();

        $collected = Payment::where('branch_id', $branchId)
            ->whereBetween('payment_date', [$r->from, $r->to])
            ->selectRaw("COALESCE(SUM(CASE WHEN type='refund' THEN -amount ELSE amount END),0) n")->value('n');

        $activeBeds = Bed::where('branch_id', $branchId)->where('is_active', true)->count();
        $occupied = Bed::where('branch_id', $branchId)->where('status', 'occupied')->count();

        return view('reports.index', [
            'r' => $r,
            'cards' => [
                'opd_visits' => Appointment::where('branch_id', $branchId)
                    ->whereBetween('scheduled_date', [$r->from, $r->to])
                    ->where('status', '!=', 'cancelled')->count(),
                'ipd_admissions' => IpdAdmission::where('branch_id', $branchId)
                    ->whereBetween('admitted_at', [$r->from, $r->to])->count(),
                'occupancy' => $activeBeds ? round($occupied / $activeBeds * 100) : 0,
                'occupancy_detail' => "{$occupied} / {$activeBeds} beds",
                'collected' => $collected,
                'pharmacy_sales' => PharmacySale::completed()->where('branch_id', $branchId)
                    ->whereBetween('sale_date', [$r->from, $r->to])->sum('total'),
                'new_patients' => Patient::whereBetween('created_at', [$r->from, $r->to])->count(),
            ],
            'dailyVisits' => $this->dailySeries(
                Appointment::where('branch_id', $branchId)->where('status', '!=', 'cancelled'),
                'scheduled_date', $r
            ),
        ]);
    }

    public function opd(Request $request): View
    {
        $r = ReportRange::fromRequest($request);
        $branchId = $this->tenancy->branchId();

        $base = Appointment::where('branch_id', $branchId)->whereBetween('scheduled_date', [$r->from, $r->to]);

        $byStatus = (clone $base)->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');
        $total = $byStatus->sum();
        $noShow = $byStatus['no_show'] ?? 0;
        $attended = $total - ($byStatus['cancelled'] ?? 0);

        $byDoctor = DB::table('appointments')
            ->join('staff', 'staff.id', '=', 'appointments.doctor_id')
            ->where('appointments.branch_id', $branchId)
            ->whereBetween('appointments.scheduled_date', [$r->from, $r->to])
            ->selectRaw("staff.id, concat(coalesce(staff.salutation,''),' ',staff.first_name,' ',coalesce(staff.last_name,'')) name")
            ->selectRaw('COUNT(*) total')
            ->selectRaw("SUM(appointments.status = 'completed') completed")
            ->groupBy('staff.id', 'staff.salutation', 'staff.first_name', 'staff.last_name')
            ->orderByDesc('total')->get();

        return view('reports.opd', [
            'r' => $r,
            'byStatus' => $byStatus,
            'total' => $total,
            'newCount' => (clone $base)->where('type', 'new')->count(),
            'followupCount' => (clone $base)->where('type', 'followup')->count(),
            'walkinCount' => (clone $base)->where('source', 'walk_in')->count(),
            'noShowRate' => $attended > 0 ? round($noShow / $attended * 100, 1) : 0,
            'byDoctor' => $byDoctor,
            'daily' => $this->dailySeries((clone $base)->where('status', '!=', 'cancelled'), 'scheduled_date', $r),
        ]);
    }

    public function ipd(Request $request): View
    {
        $r = ReportRange::fromRequest($request);
        $branchId = $this->tenancy->branchId();

        $admissions = IpdAdmission::where('branch_id', $branchId)
            ->whereBetween('admitted_at', [$r->from, $r->to])->count();

        $discharged = IpdAdmission::where('branch_id', $branchId)
            ->whereNotNull('discharged_at')
            ->whereBetween('discharged_at', [$r->from, $r->to])->get();

        $alos = $discharged->isNotEmpty()
            ? round($discharged->avg(fn ($a) => max(1, $a->admitted_at->startOfDay()->diffInDays($a->discharged_at->startOfDay()) + 1)), 1)
            : 0;

        $activeBeds = Bed::where('branch_id', $branchId)->where('is_active', true)->count();
        $bedDays = $activeBeds * $r->days();

        // occupied bed-days in the window, from the movement timeline
        $usedBedDays = IpdBedMovement::query()
            ->join('ipd_admissions', 'ipd_admissions.id', '=', 'ipd_bed_movements.ipd_admission_id')
            ->where('ipd_admissions.branch_id', $branchId)
            ->where('ipd_bed_movements.started_at', '<=', $r->to)
            ->where(fn ($q) => $q->whereNull('ipd_bed_movements.ended_at')->orWhere('ipd_bed_movements.ended_at', '>=', $r->from))
            ->get(['ipd_bed_movements.started_at', 'ipd_bed_movements.ended_at'])
            ->sum(function ($m) use ($r) {
                $start = max($m->started_at->timestamp, $r->from->timestamp);
                $end = min(($m->ended_at ?? $r->to)->timestamp, $r->to->timestamp);

                return max(0, ceil(($end - $start) / 86400));
            });

        $byWard = DB::table('wards')
            ->leftJoin('beds', 'beds.ward_id', '=', 'wards.id')
            ->where('wards.branch_id', $branchId)
            ->selectRaw('wards.name, COUNT(beds.id) total')
            ->selectRaw("SUM(beds.status = 'occupied') occupied")
            ->groupBy('wards.id', 'wards.name')->orderBy('wards.name')->get();

        return view('reports.ipd', [
            'r' => $r,
            'admissions' => $admissions,
            'dischargeCount' => $discharged->count(),
            'currentCensus' => IpdAdmission::where('branch_id', $branchId)->active()->count(),
            'alos' => $alos,
            'occupancyPct' => $bedDays > 0 ? round($usedBedDays / $bedDays * 100, 1) : 0,
            'usedBedDays' => $usedBedDays,
            'bedDays' => $bedDays,
            'dischargeTypes' => $discharged->groupBy('discharge_type')->map->count(),
            'byWard' => $byWard,
        ]);
    }

    public function revenue(Request $request): View
    {
        $r = ReportRange::fromRequest($request);
        $branchId = $this->tenancy->branchId();

        $invoices = Invoice::where('branch_id', $branchId)
            ->whereBetween('invoice_date', [$r->from, $r->to])
            ->where('status', '!=', 'cancelled');

        $payments = Payment::where('branch_id', $branchId)->whereBetween('payment_date', [$r->from, $r->to]);

        return view('reports.revenue', [
            'r' => $r,
            'billedByType' => (clone $invoices)->selectRaw('type, COUNT(*) c, COALESCE(SUM(total),0) total, COALESCE(SUM(discount),0) discount')
                ->groupBy('type')->get()->keyBy('type'),
            'billedTotal' => (clone $invoices)->sum('total'),
            'discountTotal' => (clone $invoices)->sum('discount'),
            'byMode' => (clone $payments)->where('type', 'payment')->selectRaw('mode, COALESCE(SUM(amount),0) total, COUNT(*) c')
                ->groupBy('mode')->get()->keyBy('mode'),
            'collected' => (clone $payments)->where('type', 'payment')->sum('amount'),
            'refunded' => (clone $payments)->where('type', 'refund')->sum('amount'),
            'outstanding' => Invoice::where('branch_id', $branchId)->outstanding()->sum('balance'),
            'daily' => $this->dailyMoney($payments, $r),
        ]);
    }

    public function pharmacy(Request $request): View
    {
        $r = ReportRange::fromRequest($request);
        $branchId = $this->tenancy->branchId();

        $sales = PharmacySale::completed()->where('branch_id', $branchId)->whereBetween('sale_date', [$r->from, $r->to]);
        $saleIds = (clone $sales)->pluck('id');

        $items = DB::table('pharmacy_sale_items')
            ->join('medicines', 'medicines.id', '=', 'pharmacy_sale_items.medicine_id')
            ->leftJoin('medicine_batches', 'medicine_batches.id', '=', 'pharmacy_sale_items.medicine_batch_id')
            ->whereIn('pharmacy_sale_items.pharmacy_sale_id', $saleIds);

        $top = (clone $items)
            ->selectRaw("medicines.name, medicines.strength, SUM(pharmacy_sale_items.quantity) qty, SUM(pharmacy_sale_items.line_total) revenue")
            ->groupBy('medicines.id', 'medicines.name', 'medicines.strength')
            ->orderByDesc('revenue')->limit(10)->get();

        $cogs = (clone $items)->selectRaw('COALESCE(SUM(pharmacy_sale_items.quantity * COALESCE(medicine_batches.purchase_price,0)),0) c')->value('c');
        $revenue = (clone $sales)->sum('total');

        return view('reports.pharmacy', [
            'r' => $r,
            'salesCount' => (clone $sales)->count(),
            'revenue' => $revenue,
            'cogs' => $cogs,
            'margin' => $revenue - $cogs,
            'purchaseSpend' => Purchase::where('branch_id', $branchId)->whereBetween('received_date', [$r->from, $r->to])->sum('total'),
            'top' => $top,
            'stockValue' => DB::table('medicine_batches')->where('branch_id', $branchId)->where('quantity_available', '>', 0)
                ->selectRaw('COALESCE(SUM(quantity_available * purchase_price),0) v')->value('v'),
            'expiring' => DB::table('medicine_batches')->where('branch_id', $branchId)
                ->where('quantity_available', '>', 0)
                ->whereBetween('expiry_date', [today(), today()->addDays(90)])
                ->selectRaw('COUNT(*) c, COALESCE(SUM(quantity_available * purchase_price),0) v')->first(),
        ]);
    }

    public function patients(Request $request): View
    {
        $r = ReportRange::fromRequest($request);

        $base = Patient::whereBetween('created_at', [$r->from, $r->to]);

        return view('reports.patients', [
            'r' => $r,
            'total' => (clone $base)->count(),
            'byGender' => (clone $base)->selectRaw('COALESCE(gender, "unknown") g, COUNT(*) c')->groupBy('g')->pluck('c', 'g'),
            'byAge' => $this->ageBands(clone $base),
            'byCity' => (clone $base)->whereNotNull('city')->where('city', '!=', '')
                ->selectRaw('city, COUNT(*) c')->groupBy('city')->orderByDesc('c')->limit(10)->get(),
            'daily' => $this->dailySeries(clone $base, 'created_at', $r),
        ]);
    }

    // ---- helpers --------------------------------------------------------

    protected function dailySeries($query, string $column, ReportRange $r): array
    {
        $rows = $query->selectRaw("DATE({$column}) d, COUNT(*) c")
            ->whereBetween($column, [$r->from, $r->to])
            ->groupBy('d')->pluck('c', 'd');

        return $this->fillDays($r, fn ($day) => (int) ($rows[$day] ?? 0));
    }

    protected function dailyMoney($paymentQuery, ReportRange $r): array
    {
        $rows = (clone $paymentQuery)->where('type', 'payment')
            ->selectRaw('payment_date d, COALESCE(SUM(amount),0) a')
            ->groupBy('payment_date')->pluck('a', 'd');

        return $this->fillDays($r, fn ($day) => (float) ($rows[$day] ?? 0));
    }

    protected function fillDays(ReportRange $r, callable $value): array
    {
        $out = [];
        for ($d = $r->from; $d->lte($r->to); $d = $d->addDay()) {
            $key = $d->toDateString();
            $out[$key] = $value($key);
            if (count($out) > 120) {
                break; // keep charts sane for very wide ranges
            }
        }

        return $out;
    }

    protected function ageBands($query): array
    {
        $bands = ['0-12' => 0, '13-30' => 0, '31-50' => 0, '51-70' => 0, '70+' => 0, 'unknown' => 0];

        foreach ($query->whereNotNull('dob')->pluck('dob') as $dob) {
            $age = \Carbon\Carbon::parse($dob)->age;
            $bands[match (true) {
                $age <= 12 => '0-12',
                $age <= 30 => '13-30',
                $age <= 50 => '31-50',
                $age <= 70 => '51-70',
                default => '70+',
            }]++;
        }

        return $bands;
    }
}
