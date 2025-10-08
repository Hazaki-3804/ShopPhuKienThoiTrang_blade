<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class CustomerAnalyticsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return User::select([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('COALESCE(SUM(orders.total_price), 0) as total_spent'),
                DB::raw('COALESCE(AVG(orders.total_price), 0) as avg_order_value'),
                DB::raw('MAX(orders.created_at) as last_order_date')
            ])
            ->leftJoin('orders', function($join) {
                $join->on('users.id', '=', 'orders.user_id')
                     ->whereBetween('orders.created_at', [$this->startDate, $this->endDate])
                     ->where('orders.status', '!=', 'cancelled');
            })
            ->where('users.role_id', 3)
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderBy('total_spent', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tên khách hàng',
            'Email',
            'Ngày đăng ký',
            'Số đơn hàng',
            'Tổng chi tiêu (VND)',
            'Giá trị đơn trung bình (VND)',
            'Đơn hàng cuối cùng',
            'Tần suất mua hàng (ngày)'
        ];
    }

    public function map($customer): array
    {
        $daysDiff = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) ?: 1;
        $purchaseFrequency = $customer->total_orders > 0 
            ? round($daysDiff / $customer->total_orders, 1) 
            : 0;

        return [
            $customer->id,
            $customer->name,
            $customer->email,
            Carbon::parse($customer->created_at)->format('d/m/Y'),
            $customer->total_orders,
            number_format($customer->total_spent, 0, ',', '.'),
            number_format($customer->avg_order_value, 0, ',', '.'),
            $customer->last_order_date ? Carbon::parse($customer->last_order_date)->format('d/m/Y') : 'Chưa có',
            $purchaseFrequency . ' ngày'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:I' => ['alignment' => ['horizontal' => 'center']],
            'F:G' => ['alignment' => ['horizontal' => 'right']],
        ];
    }

    public function title(): string
    {
        return 'Thống kê khách hàng';
    }
}
