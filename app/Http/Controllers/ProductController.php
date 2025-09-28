<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.products.index');
    }
    public function data()
    {
        $query = Product::query()
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('product_images', 'products.id', '=', 'product_images.product_id')
            ->whereNotNull('product_images.image_url')
            ->select([
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'categories.name as category_name',
                'product_images.image_url'
            ]);
        dd($query);
        // return DataTables::of($query)
        //     ->addColumn('actions', function ($row) {
        //         return '<button class="btn btn-sm btn-outline-secondary">Edit</button>
        //             <button class="btn btn-sm btn-outline-danger">Delete</button>';
        //     })
        //     ->rawColumns(['actions'])
        //     ->make(true);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', Rule::in([0, 1])],
            'images.*' => ['nullable', 'image', 'max:2048'], // mỗi ảnh tối đa 2MB
        ]);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        $product = Product::create($data);

        // Xử lý upload ảnh
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => '/storage/' . $path,
                    'type' => 'gallery',
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('status', 'Đã thêm sản phẩm mới');
    }
    public function edit($id)
    {
        $product = Product::with('product_images')->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        return view('admin.product.edit', compact('product', 'categories'));
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'status' => ['required', Rule::in([0, 1])],
            'images.*' => ['nullable', 'image', 'max:2048'], // mỗi ảnh tối đa 2MB
        ]);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        $product->update($data);

        // Xử lý upload ảnh
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => '/storage/' . $path,
                    'type' => 'gallery',
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('status', 'Đã cập nhật sản phẩm');
    }
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('admin.products.index')->with('status', 'Đã xóa sản phẩm');
    }
}
