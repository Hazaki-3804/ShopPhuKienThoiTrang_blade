<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Cloudinary\Cloudinary;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

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
                    // Khởi tạo Cloudinary
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => config('cloudinary.cloud.cloud_name'),
                            'api_key' => config('cloudinary.cloud.api_key'),
                            'api_secret' => config('cloudinary.cloud.api_secret'),
                        ],
                        'url' => ['secure' => true]
                    ]);
                    
                    // Delete from database and Cloudinary
                    $imagesToDelete = $product->product_images()->whereIn('id', $removedImageIds)->get();
                    foreach ($imagesToDelete as $image) {
                        // Xóa từ Cloudinary nếu là URL Cloudinary
                        if ($this->isCloudinaryUrl($image->image_url)) {
                            $this->deleteCloudinaryByUrl($image->image_url, $cloudinary);
                        } else {
                            // Xóa file local nếu là ảnh cũ
                            $imagePath = str_replace(asset('storage/'), '', $image->image_url);
                            if (Storage::disk('public')->exists($imagePath)) {
                                Storage::disk('public')->delete($imagePath);
                            }
                        }
                        $image->delete();
                    }
                }
            }

            // Handle new uploaded images (Cloudinary URLs)
            if ($request->filled('uploaded_images')) {
                $uploadedFiles = json_decode($request->uploaded_images, true);
                if (is_array($uploadedFiles) && !empty($uploadedFiles)) {
                    foreach ($uploadedFiles as $cloudinaryUrl) {
                        // Lưu Cloudinary URL vào database
                        $product->product_images()->create([
                            'product_id' => $product->id,
                            'image_url' => $cloudinaryUrl,
                            'type' => 'detail'
                        ]);
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
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                    ' . htmlspecialchars($product->name) . '
                                </span>
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
                        return '<span class="badge bg-danger"><i class="fas fa-boxes mr-1"></i>Hết hàng</span>';
                    } elseif ($product->stock <= 10) {
                        return '<span class="badge bg-warning text-dark"><i class="fas fa-boxes mr-1"></i>' . $product->stock . ' (Sắp hết)</span>';
                    } else {
                        return '<span class="badge bg-primary"><i class="fas fa-boxes mr-1"></i>' . $product->stock . '</span>';
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
                    $buttons = '
                    <div class="dropdown text-center">
                        <button class="btn btn-sm btn-light border-0" type="button" id="actionsMenu' . $product->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="box-shadow:none;">
                            <i class="fas fa-ellipsis-v text-secondary"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 rounded" aria-labelledby="actionsMenu' . $product->id . '">
                    ';

                    // Xem chi tiết
                    $buttons .= '
                        <a class="dropdown-item view-product" href="#" data-toggle="modal" data-target="#viewProductModal"
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
                            data-image="' . ($product->product_images->first() ? $product->product_images->first()->image_url : '') . '">
                            <i class="fas fa-eye text-info mr-2"></i>Xem chi tiết
                        </a>
                    ';

                    // Chỉnh sửa
                    if(auth()->user()->can('edit products')){
                        $buttons .= '
                            <a class="dropdown-item" href="' . route('admin.products.edit', $product->id) . '">
                                <i class="fas fa-edit text-warning mr-2"></i>Chỉnh sửa
                            </a>
                        ';
                    }

                    // Xóa
                    if(auth()->user()->can('delete products')){
                        $buttons .= '
                            <a class="dropdown-item delete-product text-danger" href="#" data-toggle="modal" data-target="#deleteProductModal" data-id="' . $product->id . '" data-name="' . htmlspecialchars($product->name) . '">
                                <i class="fas fa-trash mr-2"></i>Xóa
                            </a>
                        ';
                    }

                    $buttons .= '</div></div>';
                    return $buttons;
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

            // Handle uploaded images (Cloudinary URLs)
            if ($request->filled('uploaded_images')) {
                $uploadedFiles = json_decode($request->uploaded_images, true);
                if (is_array($uploadedFiles) && !empty($uploadedFiles)) {
                    foreach ($uploadedFiles as $cloudinaryUrl) {
                        // Lưu Cloudinary URL vào database
                        $product->product_images()->create([
                            'product_id' => $product->id,
                            'image_url' => $cloudinaryUrl,
                            'type' => 'detail'
                        ]);
                    }
                    
                    // Xóa danh sách ảnh temp khỏi session
                    session()->forget('temp_product_images');
                }
            }

            // Kiểm tra nếu là AJAX request
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm sản phẩm mới thành công!',
                    'type' => 'success'
                ]);
            }

            
            return redirect()->route('admin.products.index')->with('success', 'Thêm sản phẩm mới thành công!');

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

            // Khởi tạo Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);
            
            // Xóa tất cả ảnh của sản phẩm trước khi xóa sản phẩm
            foreach ($product->product_images as $image) {
                // Xóa từ Cloudinary nếu là URL Cloudinary
                if ($this->isCloudinaryUrl($image->image_url)) {
                    $this->deleteCloudinaryByUrl($image->image_url, $cloudinary);
                } else {
                    // Xóa file local nếu là ảnh cũ
                    $imagePath = public_path($image->image_url);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                // Xóa record trong database
                $image->delete();
            }

            $product->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa sản phẩm thành công!',
                    'type' => 'success',
                    'redirect' => route('admin.products.index')
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Xóa sản phẩm thành công!');
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

            // Khởi tạo Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);
            
            // Xóa các sản phẩm an toàn
            $products = Product::with('product_images')->whereIn('id', $productIds)->get();
            
            // Xóa ảnh của từng sản phẩm trước
            foreach ($products as $product) {
                foreach ($product->product_images as $image) {
                    // Xóa từ Cloudinary nếu là URL Cloudinary
                    if ($this->isCloudinaryUrl($image->image_url)) {
                        $this->deleteCloudinaryByUrl($image->image_url, $cloudinary);
                    } else {
                        // Xóa file local nếu là ảnh cũ
                        $imagePath = public_path($image->image_url);
                        if (file_exists($imagePath)) {
                            unlink($imagePath);
                        }
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
                    'type' => 'success',
                    'redirect' => route('admin.products.index')
                ]);
            }

            return redirect()->route('admin.products.index')->with('success', "Đã xóa thành công {$deletedCount} sản phẩm!");
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
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB
            ]);

            // Khởi tạo Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);
            
            $image = $request->file('image');
            
            // Upload lên Cloudinary vào folder 'products'
            $uploadResult = $cloudinary->uploadApi()->upload(
                $image->getRealPath(),
                [
                    'folder' => 'products',
                    'resource_type' => 'image',
                    'transformation' => [
                        'quality' => 'auto:good',
                        'fetch_format' => 'auto'
                    ]
                ]
            );
            
            // Lưu URL vào session để theo dõi
            $tempImages = session()->get('temp_product_images', []);
            $tempImages[] = $uploadResult['secure_url'];
            session()->put('temp_product_images', $tempImages);

            return response()->json([
                'success' => true,
                'filename' => $uploadResult['secure_url'],
                'url' => $uploadResult['secure_url'],
                'public_id' => $uploadResult['public_id'],
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
            // Khởi tạo Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);
            
            $tempImages = session()->get('temp_product_images', []);
            
            // Nếu có filename cụ thể (URL), chỉ xóa ảnh đó
            if ($request->has('filename')) {
                $url = $request->filename;
                
                if ($this->isCloudinaryUrl($url)) {
                    $this->deleteCloudinaryByUrl($url, $cloudinary);
                }
                
                // Xóa khỏi session
                $tempImages = array_filter($tempImages, function($img) use ($url) {
                    return $img !== $url;
                });
                session()->put('temp_product_images', $tempImages);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa ảnh tạm thời!'
                ]);
            }
            
            // Xóa tất cả ảnh tạm từ Cloudinary
            foreach ($tempImages as $url) {
                if ($this->isCloudinaryUrl($url)) {
                    $this->deleteCloudinaryByUrl($url, $cloudinary);
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

    /**
     * Beacon cleanup: chỉ xóa các URL Cloudinary được gửi lên, không đụng session
     */
    public function clearTempImagesBeacon(Request $request)
    {
        try {
            // Khởi tạo Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);
            
            $files = $request->input('files');
            if (is_array($files) && !empty($files)) {
                foreach ($files as $url) {
                    if ($this->isCloudinaryUrl($url)) {
                        $this->deleteCloudinaryByUrl($url, $cloudinary);
                    }
                }
            }
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::warning('clearTempImagesBeacon error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Kiểm tra xem URL có phải là Cloudinary URL không
     */
    private function isCloudinaryUrl(string $url): bool
    {
        return str_contains($url, 'cloudinary.com') && str_contains($url, '/image/upload/');
    }

    /**
     * Xóa ảnh trên Cloudinary bằng URL
     */
    private function deleteCloudinaryByUrl(string $url, Cloudinary $cloudinary): void
    {
        try {
            $publicId = $this->extractPublicIdFromUrl($url);
            if ($publicId) {
                $cloudinary->uploadApi()->destroy($publicId);
                Log::info('Deleted Cloudinary image: ' . $publicId);
            }
        } catch (\Exception $e) {
            Log::warning('Cloudinary delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Trích xuất public_id từ Cloudinary URL
     */
    private function extractPublicIdFromUrl(string $url): ?string
    {
        try {
            if (!str_contains($url, 'cloudinary.com')) {
                return null;
            }

            // URL format: https://res.cloudinary.com/{cloud_name}/image/upload/v{version}/{folder}/{filename}.{ext}
            $parts = explode('/upload/', $url);
            if (count($parts) < 2) {
                return null;
            }

            $pathAfterUpload = $parts[1];
            
            // Bỏ version nếu có (vXXXXXXXXXX/)
            $pathAfterUpload = preg_replace('/^v\d+\//', '', $pathAfterUpload);
            
            // Bỏ extension
            $publicId = preg_replace('/\.[^.]+$/', '', $pathAfterUpload);

            return $publicId;
        } catch (\Exception $e) {
            Log::error('extractPublicIdFromUrl error: ' . $e->getMessage());
            return null;
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

    // ==================== IMPORT EXCEL METHODS ====================
    
    public function showImport()
    {
        $categories = Category::all();
        return view('admin.products.import', compact('categories'));
    }

    public function previewImport(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:2048'
            ]);

            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel trống hoặc không có dữ liệu!'
                ], 400);
            }

            $header = array_map('trim', $rows[0]);
            $requiredColumns = ['Tên sản phẩm', 'Danh mục', 'Giá', 'Số lượng'];
            $missingColumns = array_diff($requiredColumns, $header);

            if (!empty($missingColumns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File thiếu các cột bắt buộc: ' . implode(', ', $missingColumns)
                ], 400);
            }

            $data = [];
            $errors = [];
            $existingProducts = Product::pluck('name')->map(function($name) {
                return strtolower(trim($name));
            })->toArray();
            
            $categories = Category::pluck('id', 'name')->map(function($id, $name) {
                return ['id' => $id, 'name_lower' => strtolower(trim($name))];
            })->toArray();

            $namesInFile = [];

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNumber = $i + 1;
                
                $name = trim($row[0] ?? '');
                $categoryName = trim($row[1] ?? '');
                $price = trim($row[2] ?? '');
                $stock = trim($row[3] ?? '');
                $description = trim($row[4] ?? '');
                $imageUrl = trim($row[5] ?? '');

                $rowErrors = [];
                $nameLower = strtolower($name);

                // Validate tên sản phẩm
                if (empty($name)) {
                    $rowErrors[] = 'Tên sản phẩm không được trống';
                } elseif (mb_strlen($name) > 255) {
                    $rowErrors[] = 'Tên sản phẩm tối đa 255 ký tự';
                } elseif (in_array($nameLower, $existingProducts)) {
                    $rowErrors[] = 'Tên sản phẩm đã tồn tại trong database';
                } elseif (isset($namesInFile[$nameLower])) {
                    $rowErrors[] = 'Tên sản phẩm trùng với dòng ' . $namesInFile[$nameLower];
                }

                // Validate danh mục
                $categoryId = null;
                if (empty($categoryName)) {
                    $rowErrors[] = 'Danh mục không được trống';
                } else {
                    $categoryNameLower = strtolower($categoryName);
                    $found = false;
                    foreach ($categories as $catName => $catData) {
                        if ($catData['name_lower'] === $categoryNameLower) {
                            $categoryId = $catData['id'];
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $rowErrors[] = 'Danh mục không tồn tại';
                    }
                }

                // Validate giá
                if (empty($price)) {
                    $rowErrors[] = 'Giá không được trống';
                } elseif (!is_numeric($price) || $price < 0) {
                    $rowErrors[] = 'Giá phải là số dương';
                }

                // Validate số lượng
                if (empty($stock) && $stock !== '0') {
                    $rowErrors[] = 'Số lượng không được trống';
                } elseif (!is_numeric($stock) || $stock < 0 || floor($stock) != $stock) {
                    $rowErrors[] = 'Số lượng phải là số nguyên không âm';
                }

                // Validate URL ảnh (không bắt buộc)
                if (!empty($imageUrl)) {
                    if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $rowErrors[] = 'URL ảnh không hợp lệ';
                    } elseif (!preg_match('/^https?:\/\//i', $imageUrl)) {
                        $rowErrors[] = 'URL ảnh phải bắt đầu bằng http:// hoặc https://';
                    }
                }

                if (!empty($name)) {
                    $namesInFile[$nameLower] = $rowNumber;
                }

                $data[] = [
                    'row_number' => $rowNumber-1,
                    'name' => $name,
                    'category_name' => $categoryName,
                    'category_id' => $categoryId,
                    'price' => $price,
                    'stock' => $stock,
                    'description' => $description,
                    'image_url' => $imageUrl,
                    'slug' => !empty($name) ? Str::slug($name) : '',
                    'errors' => $rowErrors,
                    'has_error' => !empty($rowErrors)
                ];
            }

            $validRows = count(array_filter($data, fn($row) => !$row['has_error']));
            $errorRows = count($data) - $validRows;

            return response()->json([
                'success' => true,
                'data' => $data,
                'total_rows' => count($data),
                'valid_rows' => $validRows,
                'error_rows' => $errorRows,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Preview import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processImport(Request $request)
    {
        try {
            $data = $request->input('data', []);
            
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có dữ liệu để import!'
                ], 400);
            }

            $imported = 0;
            $errors = [];

            foreach ($data as $row) {
                try {
                    $product = Product::create([
                        'name' => $row['name'],
                        'slug' => $row['slug'],
                        'category_id' => $row['category_id'],
                        'price' => $row['price'],
                        'stock' => $row['stock'],
                        'description' => $row['description'] ?? null,
                        'status' => 1
                    ]);
                    
                    // Tạo product_image nếu có URL ảnh
                    if (!empty($row['image_url'])) {
                        $product->product_images()->create([
                            'product_id' => $product->id,
                            'image_url' => $row['image_url'],
                            'type' => 'detail'
                        ]);
                    }
                    
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Dòng {$row['row_number']}: " . $e->getMessage();
                }
            }

            if ($imported > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã import thành công {$imported} sản phẩm!",
                    'imported' => $imported,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có sản phẩm nào được import!',
                    'errors' => $errors
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Process import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi import!'
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Products');

            // Headers với cột URL ảnh
            $headers = ['Tên sản phẩm', 'Danh mục', 'Giá', 'Số lượng', 'Mô tả', 'URL Ảnh'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
            $sheet->getStyle('A1:F1')->getFont()->getColor()->setRGB('FFFFFF');

            // Lấy tất cả danh mục
            $categories = Category::orderBy('name')->pluck('name')->toArray();
            
            // Sample data với URL ảnh
            $sampleData = [
                ['Mắt kính thời trang', $categories[0] ?? 'Mắt kính', 150000, 50, 'Mắt kính chống tia UV', 'https://example.com/image1.jpg'],
                ['Dây chuyền bạc', $categories[1] ?? 'Trang sức', 250000, 30, 'Dây chuyền bạc 925', 'https://example.com/image2.jpg'],
                ['Túi xách da', $categories[2] ?? 'Túi xách', 350000, 20, 'Túi xách da cao cấp', 'https://example.com/image3.jpg']
            ];
            $sheet->fromArray($sampleData, null, 'A2');

            // Tạo dropdown cho cột Danh mục (B2:B1000)
            if (!empty($categories)) {
                $validation = $sheet->getCell('B2')->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Lỗi danh mục');
                $validation->setError('Vui lòng chọn danh mục từ danh sách.');
                $validation->setPromptTitle('Chọn danh mục');
                $validation->setPrompt('Chọn một danh mục từ danh sách dropdown.');
                $validation->setFormula1('"' . implode(',', $categories) . '"');

                // Apply validation cho nhiều dòng
                for ($row = 2; $row <= 1000; $row++) {
                    $sheet->getCell('B' . $row)->setDataValidation(clone $validation);
                }
            }

            // Thêm ghi chú cho cột URL Ảnh
            $sheet->getComment('F1')->getText()->createTextRun(
                "Nhập URL ảnh sản phẩm.\nVí dụ: https://example.com/image.jpg\nHoặc để trống nếu không có ảnh."
            );
            $sheet->getComment('F1')->setWidth('300pt');
            $sheet->getComment('F1')->setHeight('80pt');

            // Auto width
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Tạo sheet hướng dẫn
            $instructionSheet = $spreadsheet->createSheet();
            $instructionSheet->setTitle('Hướng dẫn');
            
            $instructions = [
                ['HƯỚNG DẪN IMPORT SẢN PHẨM'],
                [''],
                ['1. Tên sản phẩm:', 'Bắt buộc. Tối đa 255 ký tự.'],
                ['2. Danh mục:', 'Bắt buộc. Chọn từ dropdown list.'],
                ['3. Giá:', 'Bắt buộc. Nhập số, không có dấu phẩy hay chấm.'],
                ['4. Số lượng:', 'Bắt buộc. Nhập số nguyên không âm.'],
                ['5. Mô tả:', 'Không bắt buộc. Mô tả chi tiết sản phẩm.'],
                ['6. URL Ảnh:', 'Không bắt buộc. Nhập URL đầy đủ của ảnh sản phẩm.'],
                [''],
                ['LƯU Ý:'],
                ['- Không xóa dòng tiêu đề (dòng 1)'],
                ['- Danh mục phải chọn từ dropdown, không được nhập tay'],
                ['- Giá và số lượng phải là số hợp lệ'],
                ['- URL ảnh phải là đường dẫn đầy đủ (bắt đầu bằng http:// hoặc https://)'],
                ['- Có thể để trống URL ảnh nếu không có'],
                ['- Xóa các dòng mẫu trước khi nhập dữ liệu thực'],
                [''],
                ['DANH SÁCH DANH MỤC:']
            ];
            
            $instructionSheet->fromArray($instructions, null, 'A1');
            
            // Thêm danh sách danh mục
            $rowStart = count($instructions) + 1;
            foreach ($categories as $index => $category) {
                $instructionSheet->setCellValue('A' . ($rowStart + $index), ($index + 1) . '. ' . $category);
            }
            
            // Style cho sheet hướng dẫn
            $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $instructionSheet->getStyle('A3:A8')->getFont()->setBold(true);
            $instructionSheet->getStyle('A10')->getFont()->setBold(true);
            $instructionSheet->getStyle('A' . ($rowStart - 1))->getFont()->setBold(true);
            $instructionSheet->getColumnDimension('A')->setWidth(30);
            $instructionSheet->getColumnDimension('B')->setWidth(50);

            // Set active sheet về Products
            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            $fileName = 'template_import_products_' . date('YmdHis') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Download template error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải file mẫu!');
        }
    }
}
