<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getCart(): array
    {
        return session('cart', []);
    }
    private function putCart(array $cart): void
    {
        session(['cart' => $cart]);
    }

    public function index()
    {
        $cart = $this->getCart();
        // dd($cart);
        $items = collect($cart)->map(function ($line) {
            $product = Product::find($line['product_id']);
            if (!$product || (int)($product->status ?? 0) !== 1) return null;
            return [
                'product' => $product,
                'qty' => $line['qty'],
                'price' => $product->price,
                'subtotal' => $product->price * $line['qty']
            ];
        })->filter();
        $total = (int)$items->sum('subtotal');
        return view('cart.index', compact('items', 'total'));
        // Related products: same categories as items in cart
        $related = collect();
        if ($items->isNotEmpty()) {
            $categoryIds = $items->pluck('product.category_id')->unique()->filter();
            $excludeIds = $items->pluck('product.id')->unique();
            if ($categoryIds->isNotEmpty()) {
                $related = Product::with(['category', 'product_images'])
                    ->where('status', 1)
                    ->whereIn('category_id', $categoryIds)
                    ->whereNotIn('id', $excludeIds)
                    ->inRandomOrder()
                    ->take(8)
                    ->get();
            }
        }
        return view('cart.index', compact('items', 'total', 'related'));
    }

    public function add(Request $request, string $productId)
    {
        $request->validate(['qty' => ['nullable', 'integer', 'min:1']]);
        $qty = max(1, (int)$request->integer('qty'));
        $product = Product::where('status', 1)->findOrFail($productId);
        // Require login: if guest, store pending action then redirect to login
        if (!auth()->check()) {
            session(['pending_add_to_cart' => [
                'product_id' => (string)$productId,
                'qty' => $qty,
                'intended' => url()->previous(),
            ]]);
            return redirect()->route('login');
        }
        $product = Product::where('status', 1)->findOrFail($productId);
        if ($product->stock < $qty) return back()->withErrors(['qty' => 'Số lượng vượt stock']);

        $cart = $this->getCart();
        $current = $cart[$productId]['qty'] ?? 0;
        $newQty = $current + $qty;
        if ($newQty > $product->stock) return back()->withErrors(['qty' => 'Số lượng vượt stock']);
        $cart[$productId] = ['product_id' => $product->id, 'qty' => $newQty];
        $this->putCart($cart);
        return redirect()->back()->with('status', 'Đã thêm vào giỏ');
    }

    public function buyNow(Request $request, string $productId)
    {
        $request->validate(['qty' => ['nullable', 'integer', 'min:1']]);
        $qty = max(1, (int)$request->integer('qty'));
        $product = Product::where('status', 1)->findOrFail($productId);
        if ($product->stock < $qty) return back()->withErrors(['qty' => 'Số lượng vượt stock']);

        $cart = $this->getCart();
        $cart[$productId] = ['product_id' => $product->id, 'qty' => $qty];
        $this->putCart($cart);
        return redirect()->route('checkout.index');
    }

    public function update(Request $request, string $productId)
    {
        $request->validate(['qty' => ['required', 'integer', 'min:1']]);
        $qty = (int)$request->integer('qty');
        $product = Product::where('status', 1)->findOrFail($productId);
        if ($qty > $product->stock) return back()->withErrors(['qty' => 'Số lượng vượt stock']);
        $cart = $this->getCart();
        if (!isset($cart[$productId])) return back();
        $cart[$productId]['qty'] = $qty;
        $this->putCart($cart);
        return back();
    }

    public function remove(string $productId)
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        $this->putCart($cart);
        return back();
    }
}
