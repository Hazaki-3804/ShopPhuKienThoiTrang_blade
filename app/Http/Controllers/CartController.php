<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function currentCart(): ?Cart
    {
        if (!auth()->check()) return null;
        /** @var \App\Models\User $user */
        $user = auth()->user();
        // Get or create a cart for the user
        return Cart::firstOrCreate(['user_id' => $user->id], ['user_id' => $user->id]);
    }

    public function index()
    {
        $items = collect();
        $total = 0;
        $related = collect();

        if (auth()->check()) {
            $cart = $this->currentCart();
            if ($cart) {
                $activeItems = CartItem::where('cart_id', $cart->id)
                    ->with(['product.product_images', 'product.category'])
                    ->join('products', 'products.id', '=', 'cart_items.product_id')
                    ->where('products.status', 1)
                    ->select('cart_items.*')
                    ->get();

                $items = $activeItems->map(function ($ci) {
                    $p = $ci->product;
                    if (!$p) return null;
                    $price = (int)$p->price;
                    $qty = (int)$ci->quantity;
                    return [
                        'product' => $p,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $price * $qty,
                    ];
                })->filter();

                $total = (int)$items->sum('subtotal');

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
        if ($product->stock < $qty) return back()->withErrors(['qty' => 'Số lượng vượt stock']);

        $cart = $this->currentCart();
        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_id' => (int)$product->id,
        ]);
        $current = (int)($item->exists ? $item->quantity : 0);
        $newQty = $current + $qty;
        if ($newQty > $product->stock) return back()->withErrors(['qty' => 'Số lượng vượt stock']);
        $item->quantity = $newQty;
        $item->save();
        return redirect()->back()->with('success', 'Đã thêm vào giỏ hàng thành công');
    }

    public function buyNow(Request $request, string $productId)
    {
        $request->validate(['qty' => ['nullable', 'integer', 'min:1']]);
        $qty = max(1, (int)$request->integer('qty'));
        $product = Product::where('status', 1)->findOrFail($productId);
        if ($product->stock < $qty) return back()->withErrors(['qty' => 'Số lượng vượt stock']);
        if (!auth()->check()) {
            session(['pending_buy_now' => [
                'product_id' => (string)$productId,
                'qty' => $qty,
            ]]);
            return redirect()->route('login');
        }
        
        // Lưu thông tin "Mua ngay" vào session thay vì thêm vào giỏ hàng
        session(['buy_now_item' => [
            'product_id' => (int)$product->id,
            'qty' => $qty,
        ]]);
        
        // Chuyển đến checkout với flag buy_now
        return redirect()->route('checkout.index', ['buy_now' => 1]);
    }

    public function update(Request $request, string $productId)
    {
        $request->validate(['qty' => ['required', 'integer', 'min:1']]);
        $qty = (int)$request->integer('qty');
        $product = Product::where('status', 1)->findOrFail($productId);
        if ($qty > $product->stock) return back()->withErrors(['qty' => 'Số lượng vượt stock']);
        if (!auth()->check()) return back();
        $cart = $this->currentCart();
        $item = CartItem::where('cart_id', $cart->id)->where('product_id', (int)$product->id)->first();
        if (!$item) return back();
        $item->quantity = $qty;
        $item->save();
        return back()->with('success', 'Đã cập nhật số lượng');
    }

    public function remove(string $productId)
    {
        if (!auth()->check()) return back();
        $cart = $this->currentCart();
        CartItem::where('cart_id', $cart->id)->where('product_id', (int)$productId)->delete();
        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng');
    }
}

