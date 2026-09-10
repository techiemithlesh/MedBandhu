<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManufacturerController extends Controller
{
    public function index(): View
    {
        return view('pharmacy.manufacturers.index', [
            'manufacturers' => Manufacturer::withCount('medicines')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Manufacturer::create($this->validated($request));

        return back()->with('status', 'Manufacturer added.');
    }

    public function update(Request $request, Manufacturer $manufacturer): RedirectResponse
    {
        $manufacturer->update($this->validated($request, $manufacturer));

        return back()->with('status', 'Manufacturer updated.');
    }

    public function destroy(Manufacturer $manufacturer): RedirectResponse
    {
        if ($manufacturer->medicines()->exists()) {
            return back()->with('error', 'Manufacturer is in use by medicines.');
        }
        $manufacturer->delete();

        return back()->with('status', 'Manufacturer removed.');
    }

    protected function validated(Request $request, ?Manufacturer $m = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('manufacturers', 'name')->where('hospital_id', app(Tenancy::class)->hospitalId())->ignore($m?->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
