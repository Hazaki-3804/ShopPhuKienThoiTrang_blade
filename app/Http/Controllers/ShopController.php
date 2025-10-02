<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('category', 'product_images')->where('status', 1);

        // Normalize inputs once
        $categorySlug = $request->filled('category') ? (string) $request->query('category') : null;
        $priceMin = $request->filled('price_min') ? (int) $request->query('price_min') : null;
        $priceMax = $request->filled('price_max') ? (int) $request->query('price_max') : null;
        $keyword   = $request->filled('q') ? trim((string) $request->query('q', '')) : null;
        $sort      = (string) $request->query('sort', '');

        // 1) Category (always respect if provided)
        $currentCategory = null;
        if ($categorySlug) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $currentCategory = $category;
                $query->where('category_id', $category->id);
            }
        }

        // 2) Price range
        if ($priceMin !== null) {
            $query->where('price', '>=', $priceMin);
        }
        if ($priceMax !== null) {
            $query->where('price', '<=', $priceMax);
        }

        // 3) Keyword
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // 4) Sort (last)
        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'popular') {
            $query->orderByDesc('id'); // demo popularity
        }

        $products = $query->paginate(15)->withQueryString();
        $product_images = \App\Models\ProductImage::whereIn('product_id', $products->pluck('id'))->get()->groupBy('product_id');
        //Add product image in product
        $categories = Category::orderBy('name')->get();

        // Dynamic price cap for slider (based on current category if selected)
        $basePriceQuery = Product::query()->where('status', 1);
        if ($currentCategory) {
            $basePriceQuery->where('category_id', $currentCategory->id);
        }
        $priceCap = (int) ($basePriceQuery->max('price') ?? 500000);
        if ($priceCap < 100000) {
            $priceCap = 100000;
        } // sensible minimum cap

        return view('shop.index', compact('products', 'categories', 'currentCategory', 'priceCap'));
    }

    public function show(string $id)
    {
        $product = Product::with(['category', 'reviews' => function ($q) {
            $q->latest();
        }])->where('status', 1)->findOrFail($id);
        $canReview = false;
        if (auth()->check()) {
            $userId = auth()->id();
            $canReview = \App\Models\Order::where('user_id', $userId)
                ->whereIn('status', ['paid', 'shipped'])
                ->whereHas('order_items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->exists();
        }
        return view('shop.show', compact('product', 'canReview'));
    }
}
