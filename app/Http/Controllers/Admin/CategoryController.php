<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index()
    {
        // Thống kê cho dashboard
        $stats = [
            'total_categories' => Category::count(),
            'categories_with_products' => Category::has('products')->count(),
            'categories_without_products' => Category::doesntHave('products')->count(),
            'recent_categories' => Category::where('created_at', '>=', now()->subDays(7))->count()
        ];

        return view('admin.categories.index', compact('stats'));
    }

    public function data(Request $request)
    {
        try {
            $categories = Category::withCount('products')->get();

            return DataTables::of($categories)
                ->addIndexColumn() // Thêm cột số thứ tự
                ->addColumn('checkbox', function ($category) {
                    return '<input type="checkbox" class="category-checkbox" value="' . $category->id . '">';
                })
                ->addColumn('products_count', function ($category) {
                    return '<span class="badge bg-primary">' . $category->products_count . ' sản phẩm</span>';
                })
                ->addColumn('created_date', function ($category) {
                    return $category->created_at ? $category->created_at->format('d/m/Y') : 'N/A';
                })
                ->addColumn('actions', function ($category) {
                    $editButton = '<button type="button" class="btn btn-sm btn-outline-warning edit-category" style="margin-right:6px" data-toggle="modal" data-target="#editCategoryModal" data-id="' . $category->id . '" data-name="' . htmlspecialchars($category->name) . '" data-description="' . htmlspecialchars($category->description ?? '') . '">
                        <i class="fas fa-edit"></i>
                    </button>';

                    $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-category" data-toggle="modal" data-target="#deleteCategoryModal" data-id="' . $category->id . '" data-name="' . htmlspecialchars($category->name) . '">
                        <i class="fas fa-trash"></i>
                    </button>';

                    return $editButton . $deleteButton;
                })
                ->rawColumns(['checkbox', 'products_count', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('CategoryController data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000',
            ]);

            $validated['slug'] = Str::slug($validated['name']);

            Category::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm danh mục thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.categories.index')->with('success', 'Category created successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm danh mục!',
                    'type' => 'danger',
                    'error' => $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function update(Request $request)
    {
        try {
            $category = Category::findOrFail($request->id);
            
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:1000',
            ]);

            $validated['slug'] = Str::slug($validated['name']);
            $category->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật danh mục thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật danh mục!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $category = Category::findOrFail($request->id);
            
            // Kiểm tra xem danh mục có sản phẩm không
            if ($category->products()->count() > 0) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa danh mục này vì còn có sản phẩm!',
                        'type' => 'warning'
                    ], 400);
                }
                return redirect()->back()->with('warning', 'Cannot delete category with products');
            }

            $category->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa danh mục thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa danh mục!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:categories,id'
            ]);

            $categoryIds = $validated['ids'];
            
            // Kiểm tra danh mục nào có sản phẩm
            $categoriesWithProducts = Category::whereIn('id', $categoryIds)
                ->has('products')
                ->pluck('name')
                ->toArray();

            if (!empty($categoriesWithProducts)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa các danh mục sau vì còn có sản phẩm: ' . implode(', ', $categoriesWithProducts),
                        'type' => 'warning'
                    ], 400);
                }
                return redirect()->back()->with('warning', 'Cannot delete categories with products');
            }

            // Xóa các danh mục không có sản phẩm
            $deletedCount = Category::whereIn('id', $categoryIds)->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã xóa thành công {$deletedCount} danh mục!",
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.categories.index')->with('success', "Deleted {$deletedCount} categories successfully");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa danh mục!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    // API endpoint để lấy thống kê mới
    public function getStats()
    {
        try {
            $stats = [
                'total_categories' => Category::count(),
                'categories_with_products' => Category::has('products')->count(),
                'categories_without_products' => Category::doesntHave('products')->count(),
                'recent_categories' => Category::where('created_at', '>=', now()->subDays(7))->count()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê!'
            ], 500);
        }
    }
}
