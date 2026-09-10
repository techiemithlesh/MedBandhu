<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\DrugCategory;
use App\Models\Manufacturer;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\StockMovement;
use App\Support\Tenancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        $medicines = Medicine::query()
            ->with(['category', 'manufacturer'])
            ->search($request->string('q'))
            ->when($request->filled('category'), fn ($x) => $x->where('drug_category_id', $request->integer('category')))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('pharmacy.medicines.index', [
            'medicines' => $medicines,
            'categories' => DrugCategory::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $branchId = app(Tenancy::class)->branchId();

        $results = Medicine::where('is_active', true)
            ->search($request->string('q'))
            ->limit(12)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->display_name,
                'generic' => $m->generic_name,
                'form' => $m->form,
                'gst_rate' => (float) $m->gst_rate,
                'stock' => (int) MedicineBatch::withoutTenantScope()
                    ->where('branch_id', $branchId)->where('medicine_id', $m->id)
                    ->where('quantity_available', '>', 0)->sum('quantity_available'),
            ]);

        return response()->json(['medicines' => $results]);
    }

    public function batches(Medicine $medicine): JsonResponse
    {
        $branchId = app(Tenancy::class)->branchId();

        $batches = MedicineBatch::withoutTenantScope()
            ->where('branch_id', $branchId)
            ->where('medicine_id', $medicine->id)
            ->dispensable()
            ->get(['id', 'batch_number', 'expiry_date', 'mrp', 'sale_price', 'quantity_available']);

        return response()->json([
            'batches' => $batches->map(fn ($b) => [
                'id' => $b->id,
                'batch_number' => $b->batch_number,
                'expiry' => $b->expiry_date->format('M Y'),
                'mrp' => (float) $b->mrp,
                'sale_price' => (float) $b->sale_price,
                'available' => $b->quantity_available,
            ]),
        ]);
    }

    public function create(): View
    {
        return $this->form(new Medicine(['form' => 'tablet', 'unit' => 'tablet', 'pack_size' => 1, 'gst_rate' => 12, 'schedule' => 'none', 'is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        Medicine::create($this->validated($request));

        return redirect()->route('pharmacy.medicines.index')->with('status', 'Medicine added.');
    }

    public function show(Medicine $medicine): View
    {
        $branchId = app(Tenancy::class)->branchId();
        $medicine->load(['category', 'manufacturer']);

        return view('pharmacy.medicines.show', [
            'medicine' => $medicine,
            'batches' => MedicineBatch::withoutTenantScope()
                ->where('branch_id', $branchId)->where('medicine_id', $medicine->id)
                ->orderByDesc('expiry_date')->get(),
            'movements' => StockMovement::withoutTenantScope()
                ->where('branch_id', $branchId)->where('medicine_id', $medicine->id)
                ->with('batch')->latest()->limit(50)->get(),
        ]);
    }

    public function edit(Medicine $medicine): View
    {
        return $this->form($medicine);
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update($this->validated($request, $medicine));

        return redirect()->route('pharmacy.medicines.index')->with('status', 'Medicine updated.');
    }

    protected function form(Medicine $medicine): View
    {
        return view('pharmacy.medicines.form', [
            'medicine' => $medicine,
            'forms' => Medicine::FORMS,
            'categories' => DrugCategory::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'manufacturers' => Manufacturer::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    protected function validated(Request $request, ?Medicine $m = null): array
    {
        $hospitalId = app(Tenancy::class)->hospitalId();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'strength' => ['nullable', 'string', 'max:60'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'drug_category_id' => ['nullable', Rule::exists('drug_categories', 'id')->where('hospital_id', $hospitalId)],
            'manufacturer_id' => ['nullable', Rule::exists('manufacturers', 'id')->where('hospital_id', $hospitalId)],
            'form' => ['required', Rule::in(array_keys(Medicine::FORMS))],
            'unit' => ['required', 'string', 'max:20'],
            'pack_size' => ['required', 'integer', 'min:1', 'max:1000'],
            'hsn_code' => ['nullable', 'string', 'max:12'],
            'gst_rate' => ['required', 'numeric', 'min:0', 'max:28'],
            'schedule' => ['required', Rule::in(['none', 'H', 'H1', 'X', 'OTC'])],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        // enforce unique name+strength per hospital
        $exists = Medicine::where('hospital_id', $hospitalId)
            ->where('name', $data['name'])->where('strength', $data['strength'] ?? null)
            ->when($m, fn ($q) => $q->whereKeyNot($m->id))->exists();

        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => 'A medicine with that name and strength already exists.',
            ]);
        }

        return $data;
    }
}
