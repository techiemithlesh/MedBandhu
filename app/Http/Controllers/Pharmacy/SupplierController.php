<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('pharmacy.suppliers.index', [
            'suppliers' => Supplier::withCount('purchases')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('pharmacy.suppliers.form', ['supplier' => new Supplier(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validated($request));

        return redirect()->route('pharmacy.suppliers.index')->with('status', 'Supplier added.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('pharmacy.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validated($request, $supplier));

        return redirect()->route('pharmacy.suppliers.index')->with('status', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Supplier has purchase history.');
        }
        $supplier->delete();

        return back()->with('status', 'Supplier removed.');
    }

    protected function validated(Request $request, ?Supplier $s = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('suppliers', 'name')->where('hospital_id', app(Tenancy::class)->hospitalId())->ignore($s?->id),
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'drug_license_no' => ['nullable', 'string', 'max:40'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
