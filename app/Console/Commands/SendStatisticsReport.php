<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SendStatisticsReport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'statistics:send-report {frequency} {email}';

    /**
     * The console command description.
     */
    protected $description = 'Send automated statistics report via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $frequency = $this->argument('frequency');
        $email = $this->argument('email');

        $this->info("Generating {$frequency} statistics report for {$email}...");

        try {
            $reportData = $this->generateReportData($frequency);
            $this->sendReport($reportData, $email, $frequency);
            $this->info('Report sent successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to send report: ' . $e->getMessage());
        }
    }

    private function generateReportData($frequency)
    {
        $endDate = Carbon::now();
        
        switch ($frequency) {
            case 'daily':
                $startDate = Carbon::now()->subDay();
                break;
            case 'weekly':
                $startDate = Carbon::now()->subWeek();
                break;
            case 'monthly':
                $startDate = Carbon::now()->subMonth();
                break;
            case 'quarterly':
                $startDate = Carbon::now()->subQuarter();
                break;
            default:
                $startDate = Carbon::now()->subMonth();
        }

        // Get summary statistics
        $summary = [
            'period' => $frequency,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_orders' => Order::whereBetween('created_at', [$startDate, $endDate])
                                  ->where('status', '!=', 'cancelled')
                                  ->count(),
            'total_revenue' => Order::whereBetween('created_at', [$startDate, $endDate])
                                   ->where('status', '!=', 'cancelled')
                                   ->sum('total_price'),
            'new_customers' => User::whereBetween('created_at', [$startDate, $endDate])
                                  ->where('role_id', 3)
                                  ->count(),
            'avg_order_value' => Order::whereBetween('created_at', [$startDate, $endDate])
                                     ->where('status', '!=', 'cancelled')
                                     ->avg('total_price')
        ];

        // Get top products
        $topProducts = DB::table('products')
            ->select([
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
            ->limit(5)
            ->get();

        return [
            'summary' => $summary,
            'top_products' => $topProducts
        ];
    }

    private function sendReport($reportData, $email, $frequency)
    {
        $subject = 'Báo cáo thống kê ' . $this->getFrequencyText($frequency) . ' - ' . now()->format('d/m/Y');
        
        Mail::send('emails.statistics-report', $reportData, function ($message) use ($email, $subject) {
            $message->to($email)
                    ->subject($subject);
        });
    }

    private function getFrequencyText($frequency)
    {
        $texts = [
            'daily' => 'hàng ngày',
            'weekly' => 'hàng tuần', 
            'monthly' => 'hàng tháng',
            'quarterly' => 'hàng quý'
        ];

        return $texts[$frequency] ?? 'định kỳ';
    }
}
