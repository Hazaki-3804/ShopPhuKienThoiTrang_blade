# 🔧 Chế Độ Bảo Trì - Maintenance Mode

## 📋 Tổng Quan
Tính năng chế độ bảo trì cho phép admin tạm thời đóng website để nâng cấp hoặc sửa chữa, trong khi vẫn cho phép admin truy cập bình thường.

---

## ✨ Tính Năng

### 1. **Middleware - MaintainMiddlware**
- ✅ Kiểm tra trạng thái bảo trì từ database
- ✅ Loại trừ admin users (có role admin)
- ✅ Loại trừ các routes quan trọng (admin/*, login, register, etc.)
- ✅ Trả về HTTP 503 status code khi bảo trì
- ✅ Hiển thị trang maintain.blade.php

### 2. **Trang Bảo Trì - maintain.blade.php**
- ✅ Giao diện hiện đại với gradient background
- ✅ Animated background với floating particles
- ✅ Icon xoay và pulse animation
- ✅ Info cards với hover effects
- ✅ Loading bar animation
- ✅ Social links
- ✅ Fully responsive (Desktop, Tablet, Mobile)

### 3. **Cài Đặt Trong Admin**
- ✅ Toggle switch trong Settings > Hệ thống
- ✅ Cập nhật real-time vào database
- ✅ Giao diện trực quan, dễ sử dụng

---

## 🔧 Cấu Hình

### Middleware Setup

**File:** `app/Http/Middleware/MaintainMiddlware.php`

```php
class MaintainMiddlware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Loại trừ admin routes và auth routes
        $excludedPaths = [
            'admin/*',
            'login',
            'register',
            'logout',
            'password/*',
        ];

        // Loại trừ admin users
        if (auth()->check() && auth()->user()->hasRole('admin')) {
            return $next($request);
        }

        // Check maintenance mode
        $settings = Setting::first();
        if ($settings && $settings->site_status == 'maintenance') {
            return response()->view('errors.maintain', [], 503);
        }
        
        return $next($request);
    }
}
```

### Routes Configuration

**File:** `routes/web.php`

```php
// Public routes with maintenance mode check
Route::middleware(['maintain_mode'])->group(function () {
    Route::get('/', fn() => redirect()->route('home'));
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    // ... other public routes
});

// Authenticated routes with maintenance mode check
Route::middleware(['auth', 'maintain_mode'])->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    // ... other authenticated routes
});

// Admin routes (NO maintenance check)
Route::middleware(['auth:web', 'checkAdmin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    // ... admin routes
});
```

### Middleware Registration

**File:** `bootstrap/app.php`

```php
use App\Http\Middleware\MaintainMiddlware;

$middleware->alias([
    'checkAdmin' => AdminMiddleware::class,
    'maintain_mode' => MaintainMiddlware::class,
    // ... other middleware
]);
```

---

## 🎨 Thiết Kế Trang Bảo Trì

### Features
- **Gradient Background**: Purple gradient (#667eea → #764ba2)
- **Animated Particles**: 9 floating particles với random timing
- **Icon Animation**: Rotating tools icon với pulse effect
- **Info Cards**: 3 cards (Sớm trở lại, An toàn dữ liệu, Nâng cấp tốt hơn)
- **Loading Bar**: Animated gradient loading bar
- **Social Links**: Facebook, Instagram, Email với hover effects

### Colors
```css
Primary Gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%)
Background: rgba(255, 255, 255, 0.95) with backdrop-filter
Text Primary: #2c3e50
Text Secondary: #6c757d
Highlight: #667eea
```

### Animations
- **slideUp**: Entry animation (0.8s)
- **pulse**: Icon pulse (2s infinite)
- **rotate**: Icon rotation (3s infinite)
- **float**: Particles floating (20-25s infinite)
- **loading**: Loading bar (2s infinite)

---

## 📱 Responsive Design

| Device | Width | Adjustments |
|--------|-------|-------------|
| Desktop | >768px | Full layout |
| Tablet | ≤768px | Smaller icon, single column cards |
| Mobile | ≤480px | Compact padding, smaller fonts |

---

## 🚀 Cách Sử Dụng

### Bật Chế Độ Bảo Trì

1. Đăng nhập admin
2. Vào **Settings** (Cài đặt)
3. Chọn tab **Hệ thống**
4. Bật switch **Chế độ bảo trì**
5. Click **Lưu cài đặt**

### Tắt Chế Độ Bảo Trì

1. Đăng nhập admin (vẫn truy cập được)
2. Vào **Settings**
3. Tab **Hệ thống**
4. Tắt switch **Chế độ bảo trì**
5. Click **Lưu cài đặt**

---

## 🔒 Bảo Mật

### Routes Được Loại Trừ
- `admin/*` - Tất cả admin routes
- `login` - Trang đăng nhập
- `register` - Trang đăng ký
- `logout` - Đăng xuất
- `password/*` - Reset password

### Users Được Loại Trừ
- Admin users (có role 'admin')
- Authenticated users với quyền admin

### Routes Không Bị Ảnh Hưởng
- Payment webhooks (sepay/callback, momo/notify)
- API payment check
- Admin dashboard và tất cả admin functions

---

## ✅ Checklist Triển Khai

- [x] Tạo MaintainMiddlware
- [x] Đăng ký middleware trong bootstrap/app.php
- [x] Áp dụng middleware vào public routes
- [x] Áp dụng middleware vào authenticated routes
- [x] Loại trừ admin routes
- [x] Thiết kế trang maintain.blade.php
- [x] Thêm animations và effects
- [x] Responsive design
- [x] Tích hợp với Settings page
- [x] Test với admin user
- [x] Test với normal user

---

## 🎯 Kết Quả

Tính năng bảo trì hoàn chỉnh với:
- ✨ **Giao diện đẹp**: Modern, animated, professional
- 🔒 **Bảo mật**: Admin vẫn truy cập được
- 🚀 **Hiệu suất**: Lightweight, fast loading
- 📱 **Responsive**: Hoạt động tốt mọi thiết bị
- 🎨 **UX tốt**: Thông báo rõ ràng, animations mượt

---

**Files đã tạo/chỉnh sửa:**
- `app/Http/Middleware/MaintainMiddlware.php` (New)
- `resources/views/errors/maintain.blade.php` (New)
- `routes/web.php` (Modified)
- `bootstrap/app.php` (Modified)

**Ngày triển khai:** 30/10/2025
