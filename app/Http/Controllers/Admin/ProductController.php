<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        // Thống kê cho dashboard
        $stats = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 1)->count(),
            'inactive_products' => Product::where('status', 0)->count(),
            'low_stock_products' => Product::where('stock', '<=', 10)->count(),
            'recent_products' => Product::where('created_at', '>=', now()->subDays(7))->count(),
            'out_of_stock' => Product::where('stock', 0)->count()
        ];

        $categories = Category::all();

        return view('admin.products.index', compact('stats', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function edit($id)
    {
        $product = Product::with(['category', 'product_images'])->findOrFail($id);
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:products,id',
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'status' => 'required|in:0,1',
                'description' => 'nullable|string',
                'uploaded_images' => 'nullable|string',
                'removed_images' => 'nullable|string'
            ]);

            $product = Product::findOrFail($request->id);
            
            // Update basic product info
            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'stock' => $request->stock,
                'status' => $request->status,
                'description' => $request->description
            ]);

            // Handle removed images
            if ($request->filled('removed_images')) {
                $removedImageIds = json_decode($request->removed_images, true);
                if (is_array($removedImageIds) && !empty($removedImageIds)) {
                    // Delete from database and storage
                    $imagesToDelete = $product->product_images()->whereIn('id', $removedImageIds)->get();
                    foreach ($imagesToDelete as $image) {
                        // Delete file from storage if it exists
                        $imagePath = str_replace(asset('storage/'), '', $image->image_url);
                        if (Storage::disk('public')->exists($imagePath)) {
                            Storage::disk('public')->delete($imagePath);
                        }
                        $image->delete();
                    }
                }
            }

            // Handle new uploaded images
            if ($request->filled('uploaded_images')) {
                $uploadedFiles = json_decode($request->uploaded_images, true);
                if (is_array($uploadedFiles) && !empty($uploadedFiles)) {
                    // Tạo thư mục products nếu chưa có
                    $productPath = public_path('storage/products/');
                    $this->ensureDirectoryExists($productPath);
                    
                    foreach ($uploadedFiles as $filename) {
                        // Di chuyển ảnh từ temp sang thư mục chính
                        $tempPath = public_path('storage/products/temp/' . $filename);
                        $finalPath = public_path('storage/products/' . $filename);
                        
                        if (file_exists($tempPath)) {
                            // Di chuyển file từ temp sang thư mục chính
                            if (rename($tempPath, $finalPath)) {
                                // Lưu thông tin ảnh vào database
                                $product->product_images()->create([
                                    'product_id' => $product->id,
                                    'image_url' => 'storage/products/' . $filename,
                                    'type' => 'detail'
                                ]);
                            } else {
                                Log::warning('Không thể di chuyển file: ' . $tempPath . ' -> ' . $finalPath);
                            }
                        } else {
                            Log::warning('File temp không tồn tại: ' . $tempPath);
                        }
                    }
                    
                    // Xóa danh sách ảnh temp khỏi session
                    session()->forget('temp_product_images');
                }
            }

            // Kiểm tra nếu là AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được cập nhật thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ!',
                    'errors' => $e->errors(),
                    'type' => 'danger'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('ProductController update error: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật sản phẩm!',
                    'errors' => ['general' => [$e->getMessage()]],
                    'type' => 'danger'
                ], 422);
            }
            
            return redirect()->back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function data(Request $request)
    {
        try {
            $query = Product::with(['category', 'product_images']);

            // Filter by category
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn() // Thêm cột số thứ tự
                ->addColumn('checkbox', function ($product) {
                    return '<input type="checkbox" class="product-checkbox" value="' . $product->id . '">';
                })
                ->addColumn('product_info', function ($product) {
                    $image = $product->product_images->first() 
                    ? '<img src="' . asset($product->product_images->first()->image_url) . '" width="72" height="72" style="object-fit: cover; margin-right:10px; border-radius:10px">'
                    : '<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;  margin-right:10px;"><i class="fas fa-image text-muted"></i></div>';
                    
                    return '
                    <div class="d-flex align-items-center">
                        ' . $image . '
                        <div class="mr-2" style="text-align:left;">
                            <div class="fw-bold">
                                <span style="display:inline-block; width:400px; word-wrap:break-word; white-space:normal;">' . htmlspecialchars($product->name) . '</span>
                            </div>
                            <small class="text-muted">ID: ' . $product->id . '</small>
                        </div>
                    </div>';

                })
                ->addColumn('category_name', function ($product) {
                    return $product->category ? $product->category->name : '<em class="text-muted">Chưa phân loại</em>';
                })
                ->addColumn('price_formatted', function ($product) {
                    return '<span class="fw-bold text-success">' . number_format($product->price, 0, ',', '.') . '</span>';
                })
                ->addColumn('stock_badge', function ($product) {
                    if ($product->stock == 0) {
                        return '<span class="badge bg-danger">Hết hàng</span>';
                    } elseif ($product->stock <= 10) {
                        return '<span class="badge bg-warning text-dark">' . $product->stock . ' (Sắp hết)</span>';
                    } else {
                        return '<span class="badge bg-success">' . $product->stock . '</span>';
                    }
                })
                ->addColumn('status_badge', function ($product) {
                    return $product->status == 1
                        ? '<span class="badge bg-success"><i class="fas fa-check"></i> Đang bán</span>'
                        : '<span class="badge bg-secondary"><i class="fas fa-pause"></i> Tạm dừng</span>';
                })
                ->addColumn('created_date', function ($product) {
                    return $product->created_at ? $product->created_at->format('d/m/Y') : 'N/A';
                })
                ->addColumn('actions', function ($product) {
                    $viewButton = '<button type="button" class="btn btn-sm btn-outline-info view-product" style="margin-right:6px" data-toggle="modal" data-target="#viewProductModal" 
                        data-id="' . $product->id . '" 
                        data-name="' . htmlspecialchars($product->name) . '" 
                        data-description="' . htmlspecialchars($product->description ?? '') . '"
                        data-category="' . htmlspecialchars($product->category ? $product->category->name : 'Chưa phân loại') . '"
                        data-category-id="' . $product->category_id . '"
                        data-price="' . number_format($product->price, 0, ',', '.') . '"
                        data-price-raw="' . $product->price . '"
                        data-stock="' . $product->stock . '"
                        data-status="' . ($product->status == 1 ? 'Đang bán' : 'Tạm dừng') . '"
                        data-created="' . ($product->created_at ? $product->created_at->format('d/m/Y H:i') : 'N/A') . '"
                        title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </button>';

                    $editButton = '<a href="' . route('admin.products.edit', $product->id) . '" class="btn btn-sm btn-outline-warning" style="margin-right:6px" title="Chỉnh sửa">
                        <i class="fas fa-edit"></i>
                    </a>';

                    $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-product" data-toggle="modal" data-target="#deleteProductModal" data-id="' . $product->id . '" data-name="' . htmlspecialchars($product->name) . '" title="Xóa">
                        <i class="fas fa-trash"></i>
                    </button>';

                    return $viewButton . $editButton . $deleteButton;
                })
                ->rawColumns(['checkbox', 'product_info', 'category_name', 'price_formatted', 'stock_badge', 'status_badge', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('ProductController data error: ' . $e->getMessage());
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
                'description' => 'nullable|string|max:2000',
                'category_id' => 'required|integer|exists:categories,id',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'status' => 'required|integer|in:0,1',
                'uploaded_images' => 'nullable|string'
            ]);

            $product = Product::create($validated);

            // Handle uploaded images
            if ($request->filled('uploaded_images')) {
                $uploadedFiles = json_decode($request->uploaded_images, true);
                if (is_array($uploadedFiles) && !empty($uploadedFiles)) {
                    // Tạo thư mục products nếu chưa có
                    $productPath = public_path('storage/products/');
                    $this->ensureDirectoryExists($productPath);
                    
                    foreach ($uploadedFiles as $filename) {
                        // Di chuyển ảnh từ temp sang thư mục chính
                        $tempPath = public_path('storage/products/temp/' . $filename);
                        $finalPath = public_path('storage/products/' . $filename);
                        
                        if (file_exists($tempPath)) {
                            // Di chuyển file từ temp sang thư mục chính
                            if (rename($tempPath, $finalPath)) {
                                // Lưu thông tin ảnh vào database
                                $product->product_images()->create([
                                    'product_id' => $product->id,
                                    'image_url' => 'storage/products/' . $filename,
                                    'type' => 'detail'
                                ]);
                            } else {
                                Log::warning('Cannot move file: ' . $tempPath . ' -> ' . $finalPath);
                            }
                        } else {
                            Log::warning('File temp không tồn tại: ' . $tempPath);
                        }
                    }
                    
                    // Xóa danh sách ảnh temp khỏi session
                    session()->forget('temp_product_images');
                }
            }

            // Kiểm tra nếu là AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được tạo thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ!',
                    'errors' => $e->errors(),
                    'type' => 'danger'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('ProductController store error: ' . $e->getMessage());
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo sản phẩm!',
                    'errors' => ['general' => [$e->getMessage()]],
                    'type' => 'danger'
                ], 422);
            }
            
            return redirect()->back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }


    public function destroy(Request $request)
    {
        try {
            $product = Product::findOrFail($request->id);
            
            // Kiểm tra xem sản phẩm có trong giỏ hàng hoặc đơn hàng không
            if ($product->cart_items()->count() > 0 || $product->order_items()->count() > 0) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa sản phẩm này vì đã có trong giỏ hàng hoặc đơn hàng!',
                        'type' => 'warning'
                    ], 400);
                }
                return redirect()->back()->with('warning', 'Cannot delete product with orders');
            }

            // Xóa tất cả ảnh của sản phẩm trước khi xóa sản phẩm
            foreach ($product->product_images as $image) {
                // Xóa file ảnh từ storage
                $imagePath = public_path($image->image_url);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                // Xóa record trong database
                $image->delete();
            }

            $product->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa sản phẩm thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa sản phẩm!',
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
                'ids.*' => 'integer|exists:products,id'
            ]);

            $productIds = $validated['ids'];
            
            // Kiểm tra sản phẩm nào có trong giỏ hàng hoặc đơn hàng
            $productsWithOrders = Product::whereIn('id', $productIds)
                ->where(function($query) {
                    $query->has('cart_items')->orHas('order_items');
                })
                ->pluck('name')
                ->toArray();

            if (!empty($productsWithOrders)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa các sản phẩm sau vì đã có trong giỏ hàng hoặc đơn hàng: ' . implode(', ', $productsWithOrders),
                        'type' => 'warning'
                    ], 400);
                }
                return redirect()->back()->with('warning', 'Cannot delete products with orders');
            }

            // Xóa các sản phẩm an toàn
            $products = Product::with('product_images')->whereIn('id', $productIds)->get();
            
            // Xóa ảnh của từng sản phẩm trước
            foreach ($products as $product) {
                foreach ($product->product_images as $image) {
                    // Xóa file ảnh từ storage
                    $imagePath = public_path($image->image_url);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    // Xóa record trong database
                    $image->delete();
                }
            }
            
            $deletedCount = Product::whereIn('id', $productIds)->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã xóa thành công {$deletedCount} sản phẩm!",
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', "Deleted {$deletedCount} products successfully");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa sản phẩm!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB
            ]);

            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Lưu vào thư mục temp
            $tempPath = 'storage/products/temp/';
            $fullPath = public_path($tempPath);
            
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            
            $image->move($fullPath, $filename);
            
            // Lưu vào session để theo dõi
            $tempImages = session()->get('temp_product_images', []);
            $tempImages[] = $filename;
            session()->put('temp_product_images', $tempImages);

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => asset($tempPath . $filename),
                'message' => 'Upload ảnh thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('ProductController uploadImage error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi upload ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đảm bảo thư mục tồn tại và có quyền ghi
     */
    private function ensureDirectoryExists($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
        
        if (!is_writable($path)) {
            chmod($path, 0755);
        }
        
        return is_dir($path) && is_writable($path);
    }

    public function clearTempImages(Request $request)
    {
        try {
            $tempImages = session()->get('temp_product_images', []);
            $tempPath = public_path('storage/products/temp/');
            
            // Nếu có filename cụ thể, chỉ xóa file đó
            if ($request->has('filename')) {
                $filename = $request->filename;
                $filePath = $tempPath . $filename;
                
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Xóa khỏi session
                $tempImages = array_filter($tempImages, function($img) use ($filename) {
                    return $img !== $filename;
                });
                session()->put('temp_product_images', $tempImages);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa ảnh tạm thời!'
                ]);
            }
            
            // Xóa tất cả ảnh tạm
            foreach ($tempImages as $filename) {
                $filePath = $tempPath . $filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Xóa khỏi session
            session()->forget('temp_product_images');
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ' . count($tempImages) . ' ảnh tạm thời!'
            ]);

        } catch (\Exception $e) {
            Log::error('ProductController clearTempImages error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa ảnh tạm!'
            ], 500);
        }
    }

    // API endpoint để lấy thống kê mới
    public function getStats()
    {
        try {
            $stats = [
                'total_products' => Product::count(),
                'active_products' => Product::where('status', 1)->count(),
                'inactive_products' => Product::where('status', 0)->count(),
                'low_stock_products' => Product::where('stock', '<=', 10)->count(),
                'recent_products' => Product::where('created_at', '>=', now()->subDays(7))->count(),
                'out_of_stock' => Product::where('stock', 0)->count()
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
