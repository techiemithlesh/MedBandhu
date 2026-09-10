<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\DrugCategory;
use App\Support\Tenancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DrugCategoryController extends Controller
{
    public function index(): View
    {
        return view('pharmacy.categories.index', [
            'categories' => DrugCategory::withCount('medicines')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        DrugCategory::create($this->validated($request));

        return back()->with('status', 'Category added.');
    }

    public function update(Request $request, DrugCategory $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return back()->with('status', 'Category updated.');
    }

    public function destroy(DrugCategory $category): RedirectResponse
    {
        if ($category->medicines()->exists()) {
            return back()->with('error', 'Category is in use.');
        }
        $category->delete();

        return back()->with('status', 'Category removed.');
    }

    protected function validated(Request $request, ?DrugCategory $c = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('drug_categories', 'name')->where('hospital_id', app(Tenancy::class)->hospitalId())->ignore($c?->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
