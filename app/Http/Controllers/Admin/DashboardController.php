<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {            
            $stats = $this->getDashboardStats();
            Log::info('Dashboard: Stats collected', $stats);
            
            $recentOrders = $this->getRecentOrders();
            Log::info('Dashboard: Recent orders collected', ['count' => $recentOrders->count()]);
            
            $newCustomers = $this->getNewCustomers();
            Log::info('Dashboard: New customers collected', ['count' => $newCustomers->count()]);
            
            $salesData = $this->getSalesChartData();
            Log::info('Dashboard: Sales data collected', $salesData);
            
            $productsData = $this->getTopProductsData();
            Log::info('Dashboard: Products data collected', $productsData);

            return view('admin.dashboard', compact(
                'stats', 
                'recentOrders', 
                'newCustomers', 
                'salesData', 
                'productsData'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            Log::error('Dashboard error trace: ' . $e->getTraceAsString());
            
            // Return with safe default data
            return view('admin.dashboard', [
                'stats' => [
                    'total_sales' => 0,
                    'total_orders' => 0,
                    'new_customers' => 0,
                    'low_stock' => 0
                ],
                'recentOrders' => collect([]),
                'newCustomers' => collect([]),
                'salesData' => [
                    'labels' => ['01/01', '02/01', '03/01', '04/01', '05/01', '06/01', '07/01'],
                    'data' => [0, 0, 0, 0, 0, 0, 0]
                ],
                'productsData' => [
                    'labels' => [],
                    'data' => []
                ]
            ]);
        }
    }
    
    private function getDashboardStats()
    {
        try {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            
            return [
                // Tổng doanh thu từ tất cả đơn hàng
                'total_sales' => Order::where('status', 'delivered')->sum('total_price') ?? 0,
                // Tổng số đơn hàng
                'total_orders' => Order::count() ?? 0,
                // Khách hàng mới trong tháng này
                'new_customers' => User::where('role_id', '!=', 1)
                    ->whereDate('created_at', '>=', $thisMonth)
                    ->count() ?? 0,
                // Sản phẩm sắp hết hàng
                'low_stock' => Product::where('stock', '<=', 10)->count() ?? 0
            ];
        } catch (\Exception $e) {
            Log::error('getDashboardStats error: ' . $e->getMessage());
            return [
                'total_sales' => 0,
                'total_orders' => 0,
                'new_customers' => 0,
                'low_stock' => 0
            ];
        }
    }
   
    private function getRecentOrders()
    {
        try {
            return Order::with(['user'])
                ->whereNotNull('created_at')  // Chỉ lấy order có created_at
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'customer_name' => $order->user->name ?? 'Khách vãng lai',
                        'total_amount' => $order->total_price,
                        'status' => $order->status,
                        'created_at' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A'
                    ];
                });
        } catch (\Exception $e) {
            Log::error('getRecentOrders error: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    private function getNewCustomers()
    {
        try {
            return User::where('role_id', '!=', 1)
                ->whereNotNull('created_at')  // Chỉ lấy user có created_at
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A'
                    ];
                });
        } catch (\Exception $e) {
            Log::error('getNewCustomers error: ' . $e->getMessage());
            return collect([]);
        }
    }
    private function getSalesChartData()
    {
        try {
            $days = [];
            $sales = [];
            
            // Lấy dữ liệu 7 ngày gần nhất
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now('Asia/Ho_Chi_Minh')->subDays($i);
                $days[] = $date->format('d/m');
                
                // Lấy tất cả đơn hàng trong ngày (không phân biệt status để có dữ liệu)
                $dailySales = Order::whereDate('created_at', $date->toDateString())
                    ->sum('total_price') ?? 0;
                    
                $sales[] = round($dailySales / 1000000, 2); // Convert to millions
            }
            
            return [
                'labels' => $days,
                'data' => $sales
            ];
        } catch (\Exception $e) {
            Log::error('getSalesChartData error: ' . $e->getMessage());
            return [
                'labels' => ['01/01', '02/01', '03/01', '04/01', '05/01', '06/01', '07/01'],
                'data' => [0, 0, 0, 0, 0, 0, 0]
            ];
        }
    }

    
    private function getTopProductsData()
    {
        try {
            // Lấy tất cả sản phẩm đã bán (không phân biệt status để có dữ liệu)
            $topProducts = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_sold', 'desc')
                ->take(5)
                ->get();
                
            return [
                'labels' => $topProducts->pluck('name')->toArray(),
                'data' => $topProducts->pluck('total_sold')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('getTopProductsData error: ' . $e->getMessage());
            return [
                'labels' => [],
                'data' => []
            ];
        }
    }
    
    public function getStatsApi()
    {
        return response()->json($this->getDashboardStats());
    }
    
    public function getChartsApi()
    {
        return response()->json([
            'sales' => $this->getSalesChartData(),
            'products' => $this->getTopProductsData()
        ]);
    }
}
