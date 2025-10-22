<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PromotionController extends Controller
{
    public function index()
    {
        // Thống kê cho dashboard
        $stats = [
            'total_promotions' => Discount::count(),
            'active_promotions' => Discount::where('status', 1)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count(),
            'expired_promotions' => Discount::where('end_date', '<', now())->count(),
            'upcoming_promotions' => Discount::where('start_date', '>', now())->count(),
        ];

        return view('admin.promotions.index', compact('stats'));
    }

    public function data(Request $request)
    {
        try {
            $promotions = Discount::withCount('products')->get();

            return DataTables::of($promotions)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($promotion) {
                    return '<input type="checkbox" class="promotion-checkbox" value="' . $promotion->id . '">';
                })
                ->addColumn('discount_display', function ($promotion) {
                    if ($promotion->discount_type === 'percent') {
                        return '<span class="badge bg-success">' . number_format($promotion->discount_value, 0) . '%</span>';
                    } else {
                        return '<span class="badge bg-info">' . number_format($promotion->discount_value, 0, ',', '.') . '₫</span>';
                    }
                })
                ->addColumn('date_range', function ($promotion) {
                    $start = $promotion->start_date->format('d/m/Y');
                    $end = $promotion->end_date->format('d/m/Y');
                    return $start . ' - ' . $end;
                })
                ->addColumn('status_badge', function ($promotion) {
                    $now = now();
                    if ($promotion->status == 0) {
                        return '<span class="badge bg-secondary">Tắt</span>';
                    } elseif ($promotion->start_date > $now) {
                        return '<span class="badge bg-warning">Sắp diễn ra</span>';
                    } elseif ($promotion->end_date < $now) {
                        return '<span class="badge bg-danger">Đã hết hạn</span>';
                    } else {
                        return '<span class="badge bg-success">Đang hoạt động</span>';
                    }
                })
                ->addColumn('products_count', function ($promotion) {
                    return '<span class="badge bg-primary">' . $promotion->products_count . ' sản phẩm</span>';
                })
                ->addColumn('quantity_display', function ($promotion) {
                    if ($promotion->quantity === null) {
                        return '<span class="badge bg-secondary">Không giới hạn</span>';
                    }
                    $remaining = $promotion->quantity - $promotion->used_quantity;
                    $badgeClass = $remaining > 10 ? 'bg-success' : ($remaining > 0 ? 'bg-warning' : 'bg-danger');
                    return '<span class="badge ' . $badgeClass . '">' . $remaining . '/' . $promotion->quantity . '</span>';
                })
                ->addColumn('actions', function ($promotion) {
                    $editButton = '';
                    if(auth()->user()->can('edit promotions')){
                        $editButton = '<a href="' . route('admin.promotions.edit', $promotion->id) . '" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit"></i>
                    </a>';
                    }
                    $deleteButton = '';
                    if(auth()->user()->can('delete promotions')){
                        $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-promotion-btn" data-id="' . $promotion->id . '">
                        <i class="fas fa-trash"></i>
                    </button>';
                    }

                    return '<div class="btn-action">' . $editButton . $deleteButton . '</div>';
                })
                ->rawColumns(['checkbox', 'discount_display', 'status_badge', 'products_count', 'quantity_display', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('PromotionController data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        $products = Product::where('status', 1)->get();
        return view('admin.promotions.create', compact('products'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:discounts,code',
                'description' => 'nullable|string|max:500',
                'discount_type' => 'required|in:percent,amount',
                'discount_value' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|in:0,1',
                'quantity' => 'nullable|integer|min:0',
                'products' => 'nullable|array',
                'products.*' => 'exists:products,id'
            ]);

            // Validate discount value based on type
            if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giá trị giảm giá phần trăm không được vượt quá 100%',
                    'type' => 'danger'
                ], 422);
            }

            DB::beginTransaction();

            $promotion = Discount::create([
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'quantity' => $validated['quantity'] ?? null,
                'used_quantity' => 0
            ]);

            // Attach products to promotion
            if (!empty($validated['products'])) {
                $promotion->products()->attach($validated['products']);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm chương trình khuyến mãi thành công!',
                    'type' => 'success',
                    'redirect' => route('admin.promotions.index')
                ]);
            }

            return redirect()->route('admin.promotions.index')->with('success', 'Thêm chương trình khuyến mãi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PromotionController store error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm chương trình khuyến mãi!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function edit($id)
    {
        $promotion = Discount::with('products')->findOrFail($id);
        $products = Product::where('status', 1)->get();
        $selectedProducts = $promotion->products->pluck('id')->toArray();

        return view('admin.promotions.edit', compact('promotion', 'products', 'selectedProducts'));
    }

    public function update(Request $request)
    {
        try {
            $promotion = Discount::findOrFail($request->id);

            $validated = $request->validate([
                'id' => 'required|exists:discounts,id',
                'code' => 'required|string|max:50|unique:discounts,code,' . $request->id,
                'description' => 'nullable|string|max:500',
                'discount_type' => 'required|in:percent,amount',
                'discount_value' => 'required|numeric|min:0',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|in:0,1',
                'quantity' => 'nullable|integer|min:0',
                'products' => 'nullable|array',
                'products.*' => 'exists:products,id'
            ]);

            // Validate discount value based on type
            if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giá trị giảm giá phần trăm không được vượt quá 100%',
                    'type' => 'danger'
                ], 422);
            }

            DB::beginTransaction();

            $promotion->update([
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'],
                'discount_type' => $validated['discount_type'],
                'discount_value' => $validated['discount_value'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => $validated['status'],
                'quantity' => $validated['quantity'] ?? null
            ]);

            // Sync products
            if (isset($validated['products'])) {
                $promotion->products()->sync($validated['products']);
            } else {
                $promotion->products()->detach();
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật chương trình khuyến mãi thành công!',
                    'type' => 'success',
                    'redirect' => route('admin.promotions.index')
                ]);
            }

            return redirect()->route('admin.promotions.index')->with('success', 'Cập nhật chương trình khuyến mãi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PromotionController update error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật chương trình khuyến mãi!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            // Validate request
            $id = $request->input('id');

            if (!$id) {
                Log::error('PromotionController destroy: ID not provided');
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ID không hợp lệ!',
                        'type' => 'danger'
                    ], 400);
                }
                return redirect()->back()->with('error', 'ID không hợp lệ!');
            }

            $promotion = Discount::findOrFail($id);

            DB::beginTransaction();

            // Detach all products first
            $promotion->products()->detach();

            // Delete promotion
            $promotion->delete();

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa chương trình khuyến mãi thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.promotions.index')->with('success', 'Xóa chương trình khuyến mãi thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PromotionController destroy error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa chương trình khuyến mãi! ' . $e->getMessage(),
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $ids = $request->ids;

            // If ids is a JSON string, decode it
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }

            if (empty($ids) || !is_array($ids)) {
                Log::error('PromotionController destroyMultiple: Invalid IDs format', ['ids' => $ids]);
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một chương trình khuyến mãi để xóa!',
                    'type' => 'warning'
                ], 400);
            }

            DB::beginTransaction();

            // Detach all products for selected promotions
            foreach ($ids as $id) {
                $promotion = Discount::find($id);
                if ($promotion) {
                    $promotion->products()->detach();
                }
            }

            // Delete promotions
            Discount::whereIn('id', $ids)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa ' . count($ids) . ' chương trình khuyến mãi thành công!',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PromotionController destroyMultiple error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa chương trình khuyến mãi! ' . $e->getMessage(),
                'type' => 'danger'
            ], 500);
        }
    }
}
