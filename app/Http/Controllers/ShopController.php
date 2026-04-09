<?php

namespace App\Http\Controllers;

use App\Models\MlmProduct;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function __invoke(): View
    {
        $categoryBlueprint = collect(config('mlm.commerce.categories'));

        $products = MlmProduct::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (MlmProduct $product): array {
                return [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category,
                    'category_label' => collect(config('mlm.commerce.categories'))
                        ->firstWhere('key', $product->category)['label'] ?? ucfirst($product->category),
                    'price' => (float) $product->price,
                    'bv' => (float) $product->bv,
                    'retail_commission_rate' => (float) $product->retail_commission_rate,
                    'team_bonus_rate' => (float) $product->team_bonus_rate,
                    'team_volume' => (float) $product->bv,
                    'description' => $product->description,
                    'commission_amount' => round((float) $product->price * (float) $product->retail_commission_rate, 2),
                ];
            })
            ->values();

        $categories = $categoryBlueprint
            ->map(function (array $category) use ($products): array {
                $categoryProducts = $products->where('category', $category['key']);

                return [
                    ...$category,
                    'product_count' => $categoryProducts->count(),
                    'starting_price' => $categoryProducts->min('price'),
                    'top_bv' => $categoryProducts->max('bv'),
                ];
            })
            ->values();

        return view('mlm.shop', [
            'categories' => $categories,
            'products' => $products,
            'productStats' => [
                'active_skus' => $products->count(),
                'category_count' => $categories->count(),
                'top_commission_rate' => round((float) ($products->max('retail_commission_rate') ?? 0) * 100, 2),
                'top_bv' => round((float) ($products->max('bv') ?? 0), 2),
            ],
            'rules' => config('mlm.commerce.rules'),
            'strategy' => config('mlm.strategy'),
        ]);
    }
}
