<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Service;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        return view('billing.services.index', [
            'services' => Service::with('department')
                ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
                ->orderBy('category')->orderBy('name')
                ->get(),
            'categories' => Service::CATEGORIES,
        ]);
    }

    public function create(): View
    {
        return $this->form(new Service(['category' => 'procedure', 'gst_rate' => 0, 'is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        Service::create($this->validated($request));

        return redirect()->route('billing.services.index')->with('status', 'Service added.');
    }

    public function edit(Service $service): View
    {
        return $this->form($service);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validated($request, $service));

        return redirect()->route('billing.services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return back()->with('status', 'Service removed.');
    }

    protected function form(Service $service): View
    {
        return view('billing.services.form', [
            'service' => $service,
            'categories' => Service::CATEGORIES,
            'departments' => Department::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    protected function validated(Request $request, ?Service $s = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('services', 'code')->where('hospital_id', app(Tenancy::class)->hospitalId())->ignore($s?->id),
            ],
            'category' => ['required', Rule::in(array_keys(Service::CATEGORIES))],
            'department_id' => ['nullable', Rule::exists('departments', 'id')],
            'price' => ['required', 'numeric', 'min:0'],
            'gst_rate' => ['required', 'numeric', 'min:0', 'max:28'],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = Str::upper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
