<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MlmProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.products', [
            'products' => MlmProduct::query()
                ->withCount('orderItems')
                ->withSum('orderItems as sold_units', 'quantity')
                ->withSum('orderItems as sold_volume', 'line_total')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(12),
            'categories' => collect(config('mlm.commerce.categories'))
                ->mapWithKeys(fn (array $category): array => [$category['key'] => $category['label']])
                ->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name'))),
        ]);

        $data = $this->validateProduct($request);

        MlmProduct::query()->create($data);

        return back()->with('status', 'Product created successfully.');
    }

    public function update(Request $request, MlmProduct $product): RedirectResponse
    {
        $request->merge([
            'slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name'))),
        ]);

        $data = $this->validateProduct($request, $product);

        $product->update($data);

        return back()->with('status', 'Product updated successfully.');
    }

    public function destroy(MlmProduct $product): RedirectResponse
    {
        $product->delete();

        return back()->with('status', 'Product deleted successfully.');
    }

    private function validateProduct(Request $request, ?MlmProduct $product = null): array
    {
        $categories = collect(config('mlm.commerce.categories'))
            ->pluck('key')
            ->all();

        $data = $request->validate([
            'sku' => ['required', 'string', 'max:255', Rule::unique('mlm_products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('mlm_products', 'slug')->ignore($product?->id)],
            'category' => ['required', 'string', Rule::in($categories)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'bv' => ['required', 'numeric', 'min:0'],
            'retail_commission_rate' => ['required', 'numeric', 'between:0,1'],
            'team_bonus_rate' => ['required', 'numeric', 'between:0,1'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
