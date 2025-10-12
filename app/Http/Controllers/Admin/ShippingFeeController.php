<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ShippingFeeController extends Controller
{
    public function index()
    {
        $stats = [
            'total_rules' => ShippingFee::count(),
            'active_rules' => ShippingFee::where('status', true)->count(),
            'free_shipping_rules' => ShippingFee::where('is_free_shipping', true)->count(),
            'local_rules' => ShippingFee::where('area_type', 'local')->count(),
        ];

        return view('admin.shipping-fees.index', compact('stats'));
    }

    public function data(Request $request)
    {
        try {
            $shippingFees = ShippingFee::orderBy('priority', 'desc')->get();

            return DataTables::of($shippingFees)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($fee) {
                    return '<input type="checkbox" class="shipping-fee-checkbox" value="' . $fee->id . '">';
                })
                ->addColumn('area_type_badge', function ($fee) {
                    $colors = [
                        'local' => 'success',
                        'nearby' => 'info',
                        'nationwide' => 'primary'
                    ];
                    $color = $colors[$fee->area_type] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . $fee->getAreaTypeLabel() . '</span>';
                })
                ->addColumn('distance_range', function ($fee) {
                    $min = number_format($fee->min_distance, 0);
                    $max = $fee->max_distance ? number_format($fee->max_distance, 0) : '∞';
                    return $min . ' - ' . $max . ' km';
                })
                ->addColumn('fee_display', function ($fee) {
                    if ($fee->is_free_shipping) {
                        return '<span class="badge bg-success">Miễn phí</span>';
                    }
                    $base = number_format($fee->base_fee, 0, ',', '.');
                    $perKm = number_format($fee->per_km_fee, 0, ',', '.');
                    return $base . '₫ + ' . $perKm . '₫/km';
                })
                ->addColumn('min_order_display', function ($fee) {
                    return number_format($fee->min_order_value, 0, ',', '.') . '₫';
                })
                ->addColumn('status_badge', function ($fee) {
                    if ($fee->status) {
                        return '<span class="badge bg-success">Kích hoạt</span>';
                    }
                    return '<span class="badge bg-secondary">Tắt</span>';
                })
                ->addColumn('actions', function ($fee) {
                    $editButton = '<button type="button" class="btn btn-sm btn-outline-warning edit-shipping-fee" style="margin-right:6px" 
                        data-toggle="modal" 
                        data-target="#editShippingFeeModal"
                        data-id="' . $fee->id . '" 
                        data-name="' . htmlspecialchars($fee->name) . '"
                        data-area_type="' . $fee->area_type . '"
                        data-min_distance="' . $fee->min_distance . '"
                        data-max_distance="' . ($fee->max_distance ?? '') . '"
                        data-min_order_value="' . $fee->min_order_value . '"
                        data-base_fee="' . $fee->base_fee . '"
                        data-per_km_fee="' . $fee->per_km_fee . '"
                        data-max_fee="' . ($fee->max_fee ?? '') . '"
                        data-is_free_shipping="' . ($fee->is_free_shipping ? '1' : '0') . '"
                        data-priority="' . $fee->priority . '"
                        data-status="' . ($fee->status ? '1' : '0') . '"
                        data-description="' . htmlspecialchars($fee->description ?? '') . '">
                        <i class="fas fa-edit"></i>
                    </button>';

                    $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-shipping-fee" 
                        data-toggle="modal" 
                        data-target="#deleteShippingFeeModal"
                        data-id="' . $fee->id . '" 
                        data-name="' . htmlspecialchars($fee->name) . '">
                        <i class="fas fa-trash"></i>
                    </button>';

                    return $editButton . $deleteButton;
                })
                ->rawColumns(['checkbox', 'area_type_badge', 'fee_display', 'status_badge', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('ShippingFeeController data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'area_type' => 'required|in:local,nearby,nationwide',
                'min_distance' => 'required|numeric|min:0',
                'max_distance' => 'nullable|numeric|min:0',
                'min_order_value' => 'required|numeric|min:0',
                'base_fee' => 'required|numeric|min:0',
                'per_km_fee' => 'required|numeric|min:0',
                'max_fee' => 'nullable|numeric|min:0',
                'is_free_shipping' => 'boolean',
                'priority' => 'required|integer|min:0',
                'status' => 'required|boolean',
                'description' => 'nullable|string|max:1000',
            ]);

            ShippingFee::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm quy tắc phí vận chuyển thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.shipping-fees.index')->with('success', 'Thêm quy tắc phí vận chuyển thành công!');
        } catch (\Exception $e) {
            Log::error('ShippingFeeController store error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm quy tắc phí vận chuyển!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function update(Request $request)
    {
        try {
            $shippingFee = ShippingFee::findOrFail($request->id);

            $validated = $request->validate([
                'id' => 'required|exists:shipping_fees,id',
                'name' => 'required|string|max:255',
                'area_type' => 'required|in:local,nearby,nationwide',
                'min_distance' => 'required|numeric|min:0',
                'max_distance' => 'nullable|numeric|min:0',
                'min_order_value' => 'required|numeric|min:0',
                'base_fee' => 'required|numeric|min:0',
                'per_km_fee' => 'required|numeric|min:0',
                'max_fee' => 'nullable|numeric|min:0',
                'is_free_shipping' => 'boolean',
                'priority' => 'required|integer|min:0',
                'status' => 'required|boolean',
                'description' => 'nullable|string|max:1000',
            ]);

            $shippingFee->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật quy tắc phí vận chuyển thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.shipping-fees.index')->with('success', 'Cập nhật quy tắc phí vận chuyển thành công!');
        } catch (\Exception $e) {
            Log::error('ShippingFeeController update error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật quy tắc phí vận chuyển!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $shippingFee = ShippingFee::findOrFail($request->id);
            $shippingFee->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa quy tắc phí vận chuyển thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.shipping-fees.index')->with('success', 'Xóa quy tắc phí vận chuyển thành công!');
        } catch (\Exception $e) {
            Log::error('ShippingFeeController destroy error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa quy tắc phí vận chuyển!',
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

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một quy tắc để xóa!',
                    'type' => 'warning'
                ], 400);
            }

            ShippingFee::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa ' . count($ids) . ' quy tắc phí vận chuyển thành công!',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            Log::error('ShippingFeeController destroyMultiple error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa quy tắc phí vận chuyển!',
                'type' => 'danger'
            ], 500);
        }
    }
}
