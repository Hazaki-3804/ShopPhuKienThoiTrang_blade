<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CustomerAnalyticsExport;
use App\Exports\ProductAnalyticsExport;

class StatisticsController extends Controller
{
    /**
     * Display main statistics dashboard
     */
    public function index()
    {
        return view('admin.statistics.index');
    }

    /**
     * 1. CUSTOMER ANALYTICS
     */
    public function customerAnalytics(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));

        return view('admin.statistics.customers', compact('startDate', 'endDate'));
    }

    public function customerAnalyticsData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());

        $customers = User::select([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total_price), 0) as total_spent'),
                DB::raw('COALESCE(AVG(orders.total_price), 0) as avg_order_value'),
                DB::raw('MAX(orders.created_at) as last_order_date')
            ])
            ->leftJoin('orders', function($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'orders.user_id')
                     ->whereBetween('orders.created_at', [$startDate, $endDate])
                     ->where('orders.status', '!=', 'cancelled');
            })
            // ->where('users.role_id', 3) // Only customers
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderBy('total_spent', 'desc')
            ->get();

        // Calculate purchase frequency
        $customers->each(function($customer) use ($startDate, $endDate) {
            $daysDiff = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) ?: 1;
            $customer->purchase_frequency = $customer->total_orders > 0 
                ? round($daysDiff / $customer->total_orders, 1) 
                : 0;
        });

        $topSpender = $customers->first();

        // Calculate actual revenue from payments table within the same date range
        $totalActualRevenue = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => $customers,
            'summary' => [
                'total_customers' => $customers->count(),
                'active_customers' => $customers->where('total_orders', '>', 0)->count(),
                'total_revenue_expected' => $customers->sum('total_spent'),
                'total_revenue_actual' => $totalActualRevenue,
                'avg_customer_value' => $customers->avg('total_spent'),
                'top_spender_name' => optional($topSpender)->name ?? '-',
                'top_spender_amount' => optional($topSpender)->total_spent ?? 0,
            ]
        ]);
    }

    /**
     * 2. PRODUCT ANALYTICS
     */
    public function productAnalytics(Request $request)
    {
        $categories = Category::all();
        return view('admin.statistics.products', compact('categories'));
    }

    public function productAnalyticsData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());
        $categoryId = $request->get('category_id');

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
            ->leftJoin('orders', function($join) use ($startDate, $endDate) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$startDate, $endDate])
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->join('categories', 'products.category_id', '=', 'categories.id');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $products = $query->groupBy('products.id', 'products.name', 'products.price', 'products.stock', 'categories.name')
                         ->orderBy('total_revenue', 'desc')
                         ->get();

        // Calculate estimated profit (assuming 30% profit margin)
        $products->each(function($product) {
            $product->estimated_profit = $product->total_revenue * 0.3;
            $product->profit_margin = $product->avg_selling_price > 0 
                ? (($product->avg_selling_price - $product->price) / $product->avg_selling_price) * 100 
                : 0;
        });

        return response()->json([
            'success' => true,
            'data' => $products,
            'summary' => [
                'total_products' => $products->count(),
                'products_sold' => $products->where('total_sold', '>', 0)->count(),
                'total_revenue' => $products->sum('total_revenue'),
                'total_quantity_sold' => $products->sum('total_sold')
            ]
        ]);
    }

    public function productChartData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());
        $limit = $request->get('limit', 10);

        // Top selling products
        $topProducts = Product::select([
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            ])
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', function($join) use ($startDate, $endDate) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$startDate, $endDate])
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        // Revenue by category
        $categoryRevenue = Category::select([
                'categories.name',
                DB::raw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as revenue'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as quantity_sold')
            ])
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function($join) use ($startDate, $endDate) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereBetween('orders.created_at', [$startDate, $endDate])
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('revenue', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'topProducts' => $topProducts,
            'categoryRevenue' => $categoryRevenue
        ]);
    }

    /**
     * 3. TIME-BASED ANALYTICS
     */
    public function timeAnalytics(Request $request)
    {
        return view('admin.statistics.time');
    }

    public function timeAnalyticsData(Request $request)
    {
        try {
            $period = $request->get('period', 'month'); // day, week, month, quarter, year
            $startDate = Carbon::parse($request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d')));
            $endDate = Carbon::parse($request->get('end_date', Carbon::now()->format('Y-m-d')));

            $dateFormat = $this->getDateFormat($period);
            $groupBy = $this->getGroupBy($period);

            // Build query with consistent SELECT and GROUP BY using the same expression
            $periodExpression = $this->getPeriodExpression($period);
            
            $timeData = Order::select([
                    DB::raw("{$periodExpression} as period"),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('COALESCE(SUM(total_price), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(total_price), 0) as avg_order_value'),
                    DB::raw('COUNT(DISTINCT user_id) as unique_customers')
                ])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->groupBy(DB::raw($periodExpression))
                ->orderBy('period')
                ->get();

        // Calculate growth rates
        $timeData->each(function($item, $index) use ($timeData) {
            if ($index > 0) {
                $previous = $timeData[$index - 1];
                $item->revenue_growth = $previous->total_revenue > 0 
                    ? (($item->total_revenue - $previous->total_revenue) / $previous->total_revenue) * 100 
                    : 0;
                $item->order_growth = $previous->total_orders > 0 
                    ? (($item->total_orders - $previous->total_orders) / $previous->total_orders) * 100 
                    : 0;
            } else {
                $item->revenue_growth = 0;
                $item->order_growth = 0;
            }
        });

        // Summary statistics
        $summary = [
            'total_orders' => $timeData->sum('total_orders') ?: 0,
            'total_revenue' => $timeData->sum('total_revenue') ?: 0,
            'avg_order_value' => $timeData->avg('avg_order_value') ?: 0,
            'unique_customers' => Order::whereBetween('created_at', [$startDate, $endDate])
                                     ->where('status', '!=', 'cancelled')
                                     ->distinct('user_id')
                                     ->count(),
            'avg_revenue_growth' => $timeData->avg('revenue_growth') ?: 0,
            'avg_order_growth' => $timeData->avg('order_growth') ?: 0
        ];

        return response()->json([
            'success' => true,
            'data' => $timeData,
            'summary' => $summary
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải dữ liệu thống kê: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * EXPORT FUNCTIONS
     */
    public function exportCustomersExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());

        return Excel::download(new CustomerAnalyticsExport($startDate, $endDate), 
            'phan-tich-khach-hang-' . date('d-m-Y') . '.xlsx');
    }

    public function exportCustomersPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());

        $customers = $this->getCustomerAnalyticsData($startDate, $endDate);

        $pdf = Pdf::loadView('admin.statistics.exports.customers-pdf', compact('customers', 'startDate', 'endDate'));
        return $pdf->download('phan-tich-khach-hang-' . date('d-m-Y') . '.pdf');
    }

    public function exportProductsExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30));
        $endDate = $request->get('end_date', Carbon::now());
        $categoryId = $request->get('category_id');

        return Excel::download(new ProductAnalyticsExport($startDate, $endDate, $categoryId), 
            'phan-tich-san-pham-' . date('d-m-Y') . '.xlsx');
    }

    /**
     * HELPER METHODS
     */
    private function getDateFormat($period)
    {
        switch ($period) {
            case 'day':
                return '%Y-%m-%d';
            case 'week':
                return '%Y-%u';
            case 'month':
                return '%Y-%m';
            case 'quarter':
                return '%Y-Q%q';
            case 'year':
                return '%Y';
            default:
                return '%Y-%m';
        }
    }

    private function getGroupBy($period)
    {
        switch ($period) {
            case 'day':
                return 'DATE(created_at)';
            case 'week':
                return 'YEARWEEK(created_at)';
            case 'month':
                return 'YEAR(created_at), MONTH(created_at)';
            case 'quarter':
                return 'YEAR(created_at), QUARTER(created_at)';
            case 'year':
                return 'YEAR(created_at)';
            default:
                return 'YEAR(created_at), MONTH(created_at)';
        }
    }

    private function getSelectPeriod($period)
    {
        switch ($period) {
            case 'day':
                return 'DATE(created_at)';
            case 'week':
                return 'CONCAT(YEAR(created_at), "-W", LPAD(WEEK(created_at), 2, "0"))';
            case 'month':
                return 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))';
            case 'quarter':
                return 'CONCAT(YEAR(created_at), "-Q", QUARTER(created_at))';
            case 'year':
                return 'YEAR(created_at)';
            default:
                return 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))';
        }
    }

    private function getGroupByClause($period)
    {
        switch ($period) {
            case 'day':
                return 'DATE(created_at)';
            case 'week':
                return 'YEAR(created_at), WEEK(created_at)';
            case 'month':
                return 'YEAR(created_at), MONTH(created_at)';
            case 'quarter':
                return 'YEAR(created_at), QUARTER(created_at)';
            case 'year':
                return 'YEAR(created_at)';
            default:
                return 'YEAR(created_at), MONTH(created_at)';
        }
    }

    private function getPeriodExpression($period)
    {
        switch ($period) {
            case 'day':
                return 'DATE(created_at)';
            case 'week':
                return 'CONCAT(YEAR(created_at), "-W", LPAD(WEEK(created_at), 2, "0"))';
            case 'month':
                return 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))';
            case 'quarter':
                return 'CONCAT(YEAR(created_at), "-Q", QUARTER(created_at))';
            case 'year':
                return 'YEAR(created_at)';
            default:
                return 'CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at), 2, "0"))';
        }
    }

    private function getCustomerAnalyticsData($startDate, $endDate)
    {
        return User::select([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total_price), 0) as total_spent'),
                DB::raw('COALESCE(AVG(orders.total_price), 0) as avg_order_value')
            ])
            ->leftJoin('orders', function($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'orders.user_id')
                     ->whereBetween('orders.created_at', [$startDate, $endDate])
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->where('users.role_id', 3)
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderBy('total_spent', 'desc')
            ->get();
    }
}
