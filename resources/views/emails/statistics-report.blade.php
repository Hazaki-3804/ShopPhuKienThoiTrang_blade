<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Báo cáo Thống kê</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .content {
            background: white;
            padding: 30px 20px;
            border: 1px solid #e0e0e0;
            border-top: none;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 20px 0;
        }

        .summary-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007bff;
        }

        .summary-card h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 28px;
        }

        .summary-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .products-section {
            margin-top: 30px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .products-table th,
        .products-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .products-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        .products-table tr:hover {
            background-color: #f5f5f5;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 10px 10px;
            border: 1px solid #e0e0e0;
            border-top: none;
            color: #666;
            font-size: 12px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .btn:hover {
            background: #0056b3;
        }

        @media (max-width: 600px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📊 Báo cáo Thống kê {{ ucfirst($summary['period']) }}</h1>
        <p>Từ {{ \Carbon\Carbon::parse($summary['start_date'])->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($summary['end_date'])->format('d/m/Y') }}</p>
    </div>

    <div class="content">
        <h2>📈 Tổng quan</h2>

        <div class="summary-grid">
            <div class="summary-card">
                <h3>{{ number_format($summary['total_orders']) }}</h3>
                <p>Tổng đơn hàng</p>
            </div>
            <div class="summary-card">
                <h3>{{ number_format($summary['total_revenue'], 0, ',', '.') }}</h3>
                <p>Doanh thu (VND)</p>
            </div>
            <div class="summary-card">
                <h3>{{ number_format($summary['new_customers']) }}</h3>
                <p>Khách hàng mới</p>
            </div>
            <div class="summary-card">
                <h3>{{ number_format($summary['avg_order_value'], 0, ',', '.') }}</h3>
                <p>Giá trị đơn TB (VND)</p>
            </div>
        </div>

        <div class="products-section">
            <h2>🏆 Top 5 Sản phẩm bán chạy</h2>

            @if($top_products->count() > 0)
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng bán</th>
                        <th>Doanh thu (VND)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($top_products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format($product->total_sold) }}</td>
                        <td>{{ number_format($product->revenue, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>Không có dữ liệu sản phẩm trong kỳ này.</p>
            @endif
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="{{ route('statistics.index') }}" class="btn">
                Xem báo cáo chi tiết
            </a>
        </div>
    </div>

    <div class="footer">
        <p><strong>Shop Nàng thơ</strong></p>
        <p>Báo cáo được tạo tự động vào {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>© {{ date('Y') }} Tất cả quyền được bảo lưu</p>
    </div>
</body>

</html>