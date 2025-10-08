# 📊 Statistics Module Setup Guide

## Tổng quan
Module thống kê cho hệ thống thương mại điện tử với 3 chức năng chính:
1. **Thống kê khách hàng** - Phân tích hành vi và giá trị khách hàng
2. **Thống kê sản phẩm** - Doanh số, lợi nhuận và biểu đồ trực quan
3. **Thống kê thời gian** - Xu hướng theo ngày/tháng/quý/năm

## 🚀 Cài đặt Dependencies

### 1. Cài đặt Laravel Excel (cho xuất Excel)
```bash
composer require maatwebsite/excel
```

### 2. Cài đặt DomPDF (cho xuất PDF)
```bash
composer require barryvdh/laravel-dompdf
```

### 3. Publish config files (tùy chọn)
```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

## 📁 Files đã tạo

### Controllers
- `app/Http/Controllers/StatisticsController.php` - Controller chính

### Views
- `resources/views/admin/statistics/index.blade.php` - Dashboard tổng quan
- `resources/views/admin/statistics/customers.blade.php` - Thống kê khách hàng
- `resources/views/admin/statistics/products.blade.php` - Thống kê sản phẩm  
- `resources/views/admin/statistics/time.blade.php` - Thống kê thời gian

### Export Classes
- `app/Exports/CustomerAnalyticsExport.php` - Export Excel khách hàng
- `app/Exports/ProductAnalyticsExport.php` - Export Excel sản phẩm
- `resources/views/admin/statistics/exports/customers-pdf.blade.php` - Template PDF

### Email Templates
- `resources/views/emails/statistics-report.blade.php` - Email báo cáo tự động

### Console Commands
- `app/Console/Commands/SendStatisticsReport.php` - Lệnh gửi báo cáo tự động

## 🔧 Cấu hình

### 1. Routes
Routes đã được thêm vào `routes/web.php`:
```php
// Statistics module
Route::name('statistics.')->group(function () {
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('index');
    Route::get('/statistics/customers', [StatisticsController::class, 'customerAnalytics'])->name('customers');
    Route::get('/statistics/products', [StatisticsController::class, 'productAnalytics'])->name('products');
    Route::get('/statistics/time', [StatisticsController::class, 'timeAnalytics'])->name('time');
    // ... các routes khác
});
```

### 2. Navigation Menu
Menu đã được cập nhật trong `config/adminlte.php`:
```php
[
    'text' => 'Thống kê & Báo cáo',
    'icon' => 'fas fa-chart-line',
    'submenu' => [
        ['text' => 'Tổng quan', 'route' => 'statistics.index'],
        ['text' => 'Thống kê khách hàng', 'route' => 'statistics.customers'],
        ['text' => 'Thống kê sản phẩm', 'route' => 'statistics.products'],
        ['text' => 'Thống kê thời gian', 'route' => 'statistics.time'],
    ],
]
```

## 📊 Tính năng

### 1. Thống kê Khách hàng
- **Dữ liệu**: Tên, email, số đơn hàng, tổng chi tiêu, tần suất mua hàng
- **Bộ lọc**: Khoảng thời gian (từ ngày - đến ngày)
- **Biểu đồ**: Phân khúc khách hàng, xu hướng đăng ký
- **Export**: Excel, PDF

### 2. Thống kê Sản phẩm  
- **Dữ liệu**: Số lượng bán, doanh thu, lợi nhuận ước tính
- **Bộ lọc**: Danh mục, thương hiệu, khoảng thời gian
- **Biểu đồ**: Top sản phẩm bán chạy, doanh thu theo danh mục, phân tích hiệu suất
- **Export**: Excel

### 3. Thống kê Thời gian
- **Mốc thời gian**: Ngày, tuần, tháng, quý, năm
- **Dữ liệu**: Tổng đơn hàng, doanh thu, tỷ lệ tăng trưởng
- **Biểu đồ**: Xu hướng doanh thu, tỷ lệ tăng trưởng, so sánh các kỳ
- **Báo cáo tự động**: Gửi email định kỳ

## 🔄 Báo cáo Tự động

### Thiết lập Scheduler
Thêm vào `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Báo cáo hàng ngày
    $schedule->command('statistics:send-report daily admin@example.com')
             ->dailyAt('08:00');
    
    // Báo cáo hàng tuần
    $schedule->command('statistics:send-report weekly admin@example.com')
             ->weeklyOn(1, '08:00'); // Thứ 2 lúc 8h
    
    // Báo cáo hàng tháng  
    $schedule->command('statistics:send-report monthly admin@example.com')
             ->monthlyOn(1, '08:00'); // Ngày 1 hàng tháng
}
```

### Chạy Scheduler
```bash
# Chạy một lần
php artisan statistics:send-report monthly admin@example.com

# Thiết lập cron job (Linux/Mac)
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 🎨 Frontend Technologies

### Chart.js
- **Line Charts**: Xu hướng doanh thu theo thời gian
- **Bar Charts**: Top sản phẩm bán chạy
- **Pie/Doughnut Charts**: Phân khúc khách hàng, doanh thu theo danh mục
- **Scatter Charts**: Phân tích hiệu suất sản phẩm

### AJAX & Filters
- **Real-time filtering**: Không reload trang
- **Date range picker**: Chọn khoảng thời gian
- **Category filters**: Lọc theo danh mục sản phẩm
- **Search functionality**: Tìm kiếm trong bảng

## 🔒 Permissions

Module yêu cầu user có role admin (middleware `checkAdmin`). Đảm bảo:
```php
// routes/web.php
Route::middleware(['auth', 'checkAdmin'])->group(function () {
    // Statistics routes
});
```

## 🚀 Sử dụng

1. **Truy cập**: `/statistics` (Dashboard tổng quan)
2. **Khách hàng**: `/statistics/customers`
3. **Sản phẩm**: `/statistics/products`  
4. **Thời gian**: `/statistics/time`

## 🎯 Lưu ý

- **Performance**: Với dữ liệu lớn, consider caching và pagination
- **Database**: Đảm bảo có index trên các cột `created_at`, `user_id`, `product_id`
- **Memory**: Export Excel/PDF có thể tốn memory với dataset lớn
- **Timezone**: Đảm bảo cấu hình timezone đúng trong `config/app.php`

## 📈 Mở rộng

### Thêm metrics mới
1. Tạo method mới trong `StatisticsController`
2. Thêm route tương ứng
3. Tạo view và AJAX endpoint
4. Cập nhật navigation menu

### Custom Export
1. Tạo Export class mới extend các interface của Laravel Excel
2. Implement các method: `collection()`, `headings()`, `map()`
3. Thêm route và method trong controller

Chúc bạn sử dụng module thống kê hiệu quả! 🎉
