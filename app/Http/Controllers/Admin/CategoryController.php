<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                    $buttons = '
                    <div class="dropdown text-center">
                        <button class="btn btn-sm btn-light border-0" type="button" id="actionsMenu' . $category->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="box-shadow:none;">
                            <i class="fas fa-ellipsis-v text-secondary"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 rounded" aria-labelledby="actionsMenu' . $category->id . '">
                    ';

                    // Chỉnh sửa
                    if (auth()->user()->can('edit categories')) {
                        $buttons .= '
                            <a class="dropdown-item edit-category" href="#" data-toggle="modal" data-target="#editCategoryModal" data-id="' . $category->id . '" data-name="' . htmlspecialchars($category->name) . '" data-description="' . htmlspecialchars($category->description ?? '') . '">
                                <i class="fas fa-edit text-warning mr-2"></i>Chỉnh sửa
                            </a>
                        ';
                    }

                    // Xóa
                    if (auth()->user()->can('delete categories')) {
                        $buttons .= '
                            <a class="dropdown-item delete-category text-danger" href="#" data-toggle="modal" data-target="#deleteCategoryModal" data-id="' . $category->id . '" data-name="' . htmlspecialchars($category->name) . '">
                                <i class="fas fa-trash mr-2"></i>Xóa
                            </a>
                        ';
                    }

                    $buttons .= '</div></div>';
                    return $buttons;
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

    /**
     * Hiển thị trang import Excel
     */
    public function showImport()
    {
        return view('admin.categories.import');
    }

    /**
     * Preview dữ liệu từ file Excel
     */
    public function previewImport(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:2048'
            ]);

            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Kiểm tra file có dữ liệu không
            if (count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel không có dữ liệu!'
                ], 400);
            }

            // Lấy header (dòng đầu tiên)
            $header = array_map('trim', $rows[0]);

            // Kiểm tra các cột bắt buộc
            $requiredColumns = ['Tên danh mục', 'Mô tả'];
            $missingColumns = array_diff($requiredColumns, $header);

            if (!empty($missingColumns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel thiếu các cột: ' . implode(', ', $missingColumns)
                ], 400);
            }

            // Lấy dữ liệu (bỏ dòng header)
            $data = array_slice($rows, 1);

            // Validate và chuẩn bị dữ liệu preview
            $previewData = [];
            $errors = [];
            $existingNames = Category::pluck('name')->toArray();
            $nameInFile = [];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 vì bắt đầu từ dòng 2 (dòng 1 là header)

                // Bỏ qua dòng trống
                if (empty(array_filter($row))) {
                    continue;
                }

                $name = trim($row[0] ?? '');
                $description = trim($row[1] ?? '');

                $rowErrors = [];

                // Validate tên danh mục
                if (empty($name)) {
                    $rowErrors[] = 'Tên danh mục không được để trống';
                } elseif (strlen($name) > 255) {
                    $rowErrors[] = 'Tên danh mục không được vượt quá 255 ký tự';
                } elseif (in_array($name, $existingNames)) {
                    $rowErrors[] = 'Tên danh mục đã tồn tại trong hệ thống';
                } elseif (in_array($name, $nameInFile)) {
                    $rowErrors[] = 'Tên danh mục bị trùng trong file';
                } else {
                    $nameInFile[] = $name;
                }

                // Validate mô tả
                if (strlen($description) > 1000) {
                    $rowErrors[] = 'Mô tả không được vượt quá 1000 ký tự';
                }

                $previewData[] = [
                    'row_number' => $rowNumber - 1,
                    'name' => $name,
                    'description' => $description,
                    'slug' => Str::slug($name),
                    'errors' => $rowErrors,
                    'has_error' => !empty($rowErrors)
                ];

                if (!empty($rowErrors)) {
                    $errors[] = "Dòng {$rowNumber}: " . implode(', ', $rowErrors);
                }
            }

            // Kiểm tra có dữ liệu hợp lệ không
            if (empty($previewData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel không có dữ liệu hợp lệ!'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $previewData,
                'total_rows' => count($previewData),
                'valid_rows' => count(array_filter($previewData, fn($item) => !$item['has_error'])),
                'error_rows' => count(array_filter($previewData, fn($item) => $item['has_error'])),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Preview import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý import dữ liệu vào database
     */
    public function processImport(Request $request)
    {
        try {
            $validated = $request->validate([
                'data' => 'required|array',
                'data.*.name' => 'required|string|max:255',
                'data.*.description' => 'nullable|string|max:1000',
                'data.*.slug' => 'required|string|max:255'
            ]);

            $importedCount = 0;
            $errors = [];

            foreach ($validated['data'] as $item) {
                try {
                    // Kiểm tra trùng tên một lần nữa
                    if (Category::where('name', $item['name'])->exists()) {
                        $errors[] = "Danh mục '{$item['name']}' đã tồn tại";
                        continue;
                    }

                    Category::create([
                        'name' => $item['name'],
                        'description' => $item['description'] ?? null,
                        'slug' => $item['slug']
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Lỗi khi thêm '{$item['name']}': " . $e->getMessage();
                }
            }

            if ($importedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có danh mục nào được import!',
                    'errors' => $errors
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "Đã import thành công {$importedCount} danh mục!" .
                    (!empty($errors) ? " Có " . count($errors) . " lỗi." : ""),
                'imported_count' => $importedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Process import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi import dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file Excel mẫu
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header
            $sheet->setCellValue('A1', 'Tên danh mục');
            $sheet->setCellValue('B1', 'Mô tả');

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
            ];
            $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

            // Set column width
            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->getColumnDimension('B')->setWidth(50);

            // Add sample data
            $sheet->setCellValue('A2', 'Mắt kính thời trang');
            $sheet->setCellValue('B2', 'Các loại mắt kính thời trang cao cấp');

            $sheet->setCellValue('A3', 'Dây chuyền');
            $sheet->setCellValue('B3', 'Dây chuyền và vòng cổ đẹp');

            $sheet->setCellValue('A4', 'Túi xách');
            $sheet->setCellValue('B4', 'Túi xách nữ thời trang');

            // Create writer
            $writer = new Xlsx($spreadsheet);

            // Set headers for download
            $fileName = 'file_mau_import_danh_muc_san_pham' . date('Y-m-d') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            Log::error('Download template error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải file mẫu!');
        }
    }
}
