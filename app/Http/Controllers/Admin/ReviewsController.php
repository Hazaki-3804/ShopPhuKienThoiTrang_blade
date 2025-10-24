<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewsController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user'])->orderBy('created_at', 'desc');

        // Filter by visibility
        if ($request->has('visibility')) {
            if ($request->visibility === 'hidden') {
                $query->where('is_hidden', true);
            } elseif ($request->visibility === 'visible') {
                $query->where('is_hidden', false);
            }
        }

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $reviews = $query->paginate(20);

        // Stats
        $stats = [
            'total' => Review::count(),
            'visible' => Review::where('is_hidden', false)->count(),
            'hidden' => Review::where('is_hidden', true)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function toggleVisibility(Review $review)
    {
        $review->update(['is_hidden' => !$review->is_hidden]);

        $status = $review->is_hidden ? 'ẩn' : 'hiển thị';

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã {$status} bình luận thành công"
            ]);
        }

        return back()->with('success', "Đã {$status} bình luận thành công");
    }

    public function destroy(Review $review)
    {
        $review->delete();
        // Always return JSON for AJAX requests (check X-Requested-With header)
        if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa bình luận thành công'
            ]);
        }

        return back()->with('success', 'Đã xóa bình luận thành công');
    }
}
