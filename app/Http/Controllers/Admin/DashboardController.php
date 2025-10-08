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
        // Start with basic empty data to test the view first
        $stats = [
            'total_sales' => 0,
            'total_orders' => 0,
            'new_customers' => 0,
            'low_stock' => 0
        ];
        
        $recentOrders = collect([]);
        $newCustomers = collect([]);
        
        $salesData = [
            'labels' => ['01/01', '02/01', '03/01', '04/01', '05/01', '06/01', '07/01'],
            'data' => [0, 0, 0, 0, 0, 0, 0]
        ];
        
        $productsData = [
            'labels' => [],
            'data' => []
        ];

        return view('admin.dashboard', compact(
            'stats', 
            'recentOrders', 
            'newCustomers', 
            'salesData', 
            'productsData'
        ));
    }
    
    private function getDashboardStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        
        try {
            return [
                'total_sales' => Order::whereIn('status', ['delivered', 'shipped'])
                    ->sum('total_price') ?? 0,
                'total_orders' => Order::count() ?? 0,
                'new_customers' => User::where('role_id', '!=', 1)
                    ->whereDate('created_at', '>=', $thisMonth)
                    ->count() ?? 0,
                'low_stock' => Product::where('stock', '<=', 10)->count() ?? 0
            ];
        } catch (\Exception $e) {
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
        return Order::with(['user'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->user->name ?? 'Khách vãng lai',
                    'total_amount' => $order->total_price,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('d/m/Y H:i')
                ];
            });
    }
    
    private function getNewCustomers()
    {
        return User::where('role_id', '!=', 1)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format('d/m/Y')
                ];
            });
    }
    
    private function getSalesChartData()
    {
        $days = [];
        $sales = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('d/m');
            
            $dailySales = Order::whereIn('status', ['delivered', 'shipped'])
                ->whereDate('created_at', $date)
                ->sum('total_price');
                
            $sales[] = $dailySales / 1000000; // Convert to millions
        }
        
        return [
            'labels' => $days,
            'data' => $sales
        ];
    }
    
    private function getTopProductsData()
    {
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['delivered', 'shipped'])
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();
            
        return [
            'labels' => $topProducts->pluck('name')->toArray(),
            'data' => $topProducts->pluck('total_sold')->toArray()
        ];
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
