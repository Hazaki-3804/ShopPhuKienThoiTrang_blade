<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $productId)
    {
        $product = Product::where('status', 1)->findOrFail($productId);
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        // Must be logged in and match the user_id provided
        abort_unless(auth()->check() && (int)auth()->id() === (int)$data['user_id'], 403);

        // Only users who have delivered orders containing this product can review
        $eligible = \App\Models\Order::where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->whereHas('order_items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->exists();
        if (!$eligible) {
            return back()->withErrors(['review' => 'Bạn chỉ có thể đánh giá sản phẩm đã nhận hàng.']);
        }

        // Upsert: one review per user per product
        $existing = Review::where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);
            return back()->with('success', 'Đã cập nhật đánh giá');
        }

        Review::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'Người dùng',
            'user_email' => auth()->user()->email ?? null,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);
        return back()->with('success', 'Đã gửi đánh giá');
    }
}
