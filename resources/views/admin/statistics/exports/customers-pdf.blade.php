<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Báo cáo Thống kê Khách hàng</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        .report-period {
            font-size: 14px;
            color: #888;
        }
        .summary {
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .table td.number {
            text-align: right;
        }
        .table td.center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">SHOP PHỤ KIỆN THỜI TRANG</div>
        <div class="report-title">BÁO CÁO THỐNG KÊ KHÁCH HÀNG</div>
        <div class="report-period">
            Từ ngày {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} 
            đến ngày {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </div>
    </div>

    <div class="summary">
        <h3>Tổng quan</h3>
        <div class="summary-row">
            <span class="summary-label">Tổng số khách hàng:</span>
            <span>{{ number_format($customers->count()) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Khách hàng có mua hàng:</span>
            <span>{{ number_format($customers->where('total_orders', '>', 0)->count()) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Tổng doanh thu:</span>
            <span>{{ number_format($customers->sum('total_spent'), 0, ',', '.') }} VND</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Giá trị khách hàng trung bình:</span>
            <span>{{ number_format($customers->avg('total_spent'), 0, ',', '.') }} VND</span>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên khách hàng</th>
                <th>Email</th>
                <th>Ngày đăng ký</th>
                <th>Số đơn hàng</th>
                <th>Tổng chi tiêu (VND)</th>
                <th>Giá trị đơn TB (VND)</th>
                <th>Đơn hàng cuối</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $index => $customer)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $customer->name }}</td>
                <td>{{ $customer->email }}</td>
                <td class="center">{{ \Carbon\Carbon::parse($customer->created_at)->format('d/m/Y') }}</td>
                <td class="center">{{ $customer->total_orders }}</td>
                <td class="number">{{ number_format($customer->total_spent, 0, ',', '.') }}</td>
                <td class="number">{{ number_format($customer->avg_order_value, 0, ',', '.') }}</td>
                <td class="center">
                    {{ $customer->last_order_date ? \Carbon\Carbon::parse($customer->last_order_date)->format('d/m/Y') : 'Chưa có' }}
                </td>
            </tr>
            @if(($index + 1) % 25 == 0 && $index + 1 < $customers->count())
            </tbody>
        </table>
        <div class="page-break"></div>
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên khách hàng</th>
                    <th>Email</th>
                    <th>Ngày đăng ký</th>
                    <th>Số đơn hàng</th>
                    <th>Tổng chi tiêu (VND)</th>
                    <th>Giá trị đơn TB (VND)</th>
                    <th>Đơn hàng cuối</th>
                </tr>
            </thead>
            <tbody>
            @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Báo cáo được tạo tự động vào {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>© {{ date('Y') }} Shop Phụ kiện Thời trang - Tất cả quyền được bảo lưu</p>
    </div>
</body>
</html>
