<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductAnalyticsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $categoryId;

    public function __construct($startDate, $endDate, $categoryId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->categoryId = $categoryId;
    }

    public function collection()
    {
        $query = Product::select([
                'products.id',
                'products.name',
                'products.price',
                'products.stock',
                'categories.name as category_name',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as total_sold'),
                DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as total_revenue'),
                DB::raw('COALESCE(AVG(order_items.price), products.price) as avg_selling_price'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            ])
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->join('categories', 'products.category_id', '=', 'categories.id');

        if ($this->categoryId) {
            $query->where('products.category_id', $this->categoryId);
        }

        return $query->groupBy('products.id', 'products.name', 'products.price', 'products.stock', 'categories.name')
                    ->orderBy('total_revenue', 'desc')
                    ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên sản phẩm',
            'Danh mục',
            'Giá gốc (VND)',
            'Giá bán trung bình (VND)',
            'Số lượng bán',
            'Doanh thu (VND)',
            'Lợi nhuận ước tính (VND)',
            'Tỷ lệ lợi nhuận (%)',
            'Tồn kho',
            'Số đơn hàng'
        ];
    }

    public function map($product): array
    {
        $estimatedProfit = $product->total_revenue * 0.3; // Assuming 30% profit margin
        $profitMargin = $product->avg_selling_price > 0 
            ? (($product->avg_selling_price - $product->price) / $product->avg_selling_price) * 100 
            : 0;

        return [
            $product->id,
            $product->name,
            $product->category_name,
            number_format($product->price, 0, ',', '.'),
            number_format($product->avg_selling_price, 0, ',', '.'),
            $product->total_sold,
            number_format($product->total_revenue, 0, ',', '.'),
            number_format($estimatedProfit, 0, ',', '.'),
            number_format($profitMargin, 1, ',', '.') . '%',
            $product->stock,
            $product->order_count
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:K' => ['alignment' => ['horizontal' => 'center']],
            'D:I' => ['alignment' => ['horizontal' => 'right']],
        ];
    }

    public function title(): string
    {
        return 'Thống kê sản phẩm';
    }
}
