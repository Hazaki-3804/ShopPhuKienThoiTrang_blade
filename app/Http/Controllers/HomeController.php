<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        // After login: auto-complete pending add-to-cart
        if (auth()->check() && session()->has('pending_add_to_cart')) {
            $pending = session('pending_add_to_cart');
            session()->forget('pending_add_to_cart');
            if (!empty($pending['product_id']) && !empty($pending['qty'])) {
                $cart = session('cart', []);
                $pid = (string)$pending['product_id'];
                $qty = max(1, (int)$pending['qty']);
                $cart[$pid] = ['product_id' => (int)$pid, 'qty' => ($cart[$pid]['qty'] ?? 0) + $qty];
                session(['cart' => $cart]);
                return redirect()->route('cart.index')->with('status', 'Đã thêm vào giỏ sau khi đăng nhập');
            }
        }
        // Đề xuất: Lấy ngẫu nhiên 12 sản phẩm (chỉ lấy sản phẩm có ảnh) và chia thành các nhóm 4 để hiển thị theo slide
        $products = Product::with(['category', 'product_images'])
            ->where('status', 1)
            ->whereHas('product_images')
            ->inRandomOrder()
            ->take(12)
            ->get()
            ->chunk(4);

        // Ảnh nền cho banner New Arrivals (sản phẩm mới nhất)
        // $newProduct = Product::with('product_images')
        //     ->where('status', 1)
        //     ->latest('id')
        //     ->first();
        $newProduct = Banner::where('status', 1)
            ->where('type', '=', 'new_arrivals')
            ->orderByDesc('created_at')
            ->first();
        // $newBannerImage = optional(optional($newProduct)->product_images->first())->image_url;
        $newBannerImage = optional($newProduct)->image_url;

        // Ảnh nền cho banner Best Sellers (tồn kho nhiều nhất)
        // $bestProduct = Product::with('product_images')
        //     ->where('status', 1)
        //     ->orderByDesc('stock')
        //     ->first();
        $bestProduct = Banner::where('status', 1)
            ->where('type', '=', 'best_sellers')
            ->orderByDesc('created_at')
            ->first();

        // $bestBannerImage = optional(optional($bestProduct)->product_images->first())->image_url;
        $bestBannerImage = optional($bestProduct)->image_url;

        // Chuẩn hóa URL nếu là đường dẫn tương đối
        $normalize = function ($url) {
            if (!$url) return null;
            return Str::startsWith($url, ['http://', 'https://', '/']) ? $url : asset($url);
        };
        $newBannerImage = $normalize($newBannerImage);
        $bestBannerImage = $normalize($bestBannerImage);
        // dd($newBannerImage, $bestBannerImage);   
        // dd($products);
        return view('home', compact('products', 'newBannerImage', 'bestBannerImage'));
    }
}
