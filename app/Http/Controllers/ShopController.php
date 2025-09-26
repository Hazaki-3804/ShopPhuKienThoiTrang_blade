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

        // Filter category
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->string('category'))->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filter price range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (int)$request->integer('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (int)$request->integer('price_max'));
        }

        // Sort
        $sort = $request->string('sort');
        if ($sort === 'price_asc') $query->orderBy('price');
        if ($sort === 'price_desc') $query->orderByDesc('price');
        if ($sort === 'popular') $query->orderByDesc('id'); // demo popularity

        $products = $query->paginate(12)->withQueryString();
        $product_images = \App\Models\ProductImage::whereIn('product_id', $products->pluck('id'))->get()->groupBy('product_id');
        //Add product image in product
        $categories = Category::orderBy('name')->get();

        return view('shop.index', compact('products', 'categories'));
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
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->exists();
        }
        return view('shop.show', compact('product', 'canReview'));
    }
}
