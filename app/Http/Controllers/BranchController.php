<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(protected Tenancy $tenancy) {}

    public function index(): View
    {
        $hospital = $this->tenancy->hospital();

        return view('branches.index', [
            'hospital' => $hospital,
            'branches' => $hospital->branches()->withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->guardLimit();

        return $this->form(new Branch(['type' => 'clinic', 'is_active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->guardLimit();
        $data = $this->validated($request);

        $branch = $this->tenancy->hospital()->branches()->create([
            ...$data,
            'hospital_id' => $this->tenancy->hospitalId(),
        ]);

        // give the creating admin access to the new branch
        $request->user()->branches()->syncWithoutDetaching([$branch->id]);

        return redirect()->route('branches.index')->with('status', "Branch “{$branch->name}” added.");
    }

    public function edit(Branch $branch): View
    {
        return $this->form($branch);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $branch->update($this->validated($request, $branch));

        return redirect()->route('branches.index')->with('status', 'Branch updated.');
    }

    protected function guardLimit(): void
    {
        if (! $this->tenancy->hospital()->canAddBranch()) {
            throw ValidationException::withMessages([
                'branch' => 'Your plan allows '.$this->tenancy->hospital()->branch_limit.' branch(es). Upgrade to add more.',
            ]);
        }
    }

    protected function form(Branch $branch): View
    {
        return view('branches.form', ['branch' => $branch]);
    }

    protected function validated(Request $request, ?Branch $branch = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:20', 'alpha_num',
                Rule::unique('branches', 'code')->where('hospital_id', $this->tenancy->hospitalId())->ignore($branch?->id),
            ],
            'type' => ['required', Rule::in(['main', 'clinic', 'daycare', 'diagnostic'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $data['code'] = Str::upper($data['code']);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
