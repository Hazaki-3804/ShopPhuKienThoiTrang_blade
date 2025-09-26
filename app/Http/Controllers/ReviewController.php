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
        $data['product_id'] = $product->id;
        Review::create($data);
        return back()->with('status', 'Đã gửi đánh giá');
    }
}
