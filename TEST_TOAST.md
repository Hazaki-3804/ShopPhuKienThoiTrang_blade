# Hướng dẫn kiểm tra Toast Notification

## Các thay đổi đã thực hiện:

### 1. Sử dụng Event Delegation
```javascript
// Thay vì:
$('#editShippingFeeModal form').on('submit', ...)

// Đã đổi thành:
$(document).on('submit', '#editShippingFeeModal form', ...)
```
**Lý do**: Event delegation đảm bảo event handler hoạt động ngay cả khi modal được load động.

### 2. Thêm Debug Console Log
- 🚀 Form submitted: Khi form được submit
- ✅ Success response: Khi nhận response thành công
- ❌ Error response: Khi có lỗi

### 3. Disable Button khi đang xử lý
- Nút "Cập nhật" sẽ hiển thị spinner và text "Đang xử lý..."
- Tránh double submission

### 4. Toast Notification
- Position: `top-end` (góc phải trên)
- Timer: 3 giây (success), 4 giây (error)
- Progress bar
- Hover để pause

## Cách kiểm tra:

### Bước 1: Mở Console (F12)
1. Nhấn F12 để mở Developer Tools
2. Chọn tab "Console"

### Bước 2: Thực hiện cập nhật
1. Click nút "Sửa" (icon bút) ở một quy tắc bất kỳ
2. Thay đổi một giá trị (ví dụ: Tên quy tắc)
3. Click nút "Cập nhật"

### Bước 3: Quan sát Console
Bạn sẽ thấy các log:
```
🚀 Form submitted: {url: "...", modalId: "editShippingFeeModal", formData: "..."}
✅ Success response: {success: true, message: "Cập nhật quy tắc phí vận chuyển thành công!", type: "success"}
```

### Bước 4: Kiểm tra Toast
- Toast sẽ xuất hiện ở **góc phải trên** màn hình
- Màu xanh lá với icon ✓
- Text: "Cập nhật quy tắc phí vận chuyển thành công!"
- Tự động ẩn sau 3 giây

## Nếu Toast vẫn không hiện:

### Kiểm tra 1: SweetAlert2 đã load chưa?
Mở Console và gõ:
```javascript
typeof Swal
```
Kết quả phải là: `"object"` hoặc `"function"`

Nếu là `"undefined"`, SweetAlert2 chưa được load.

### Kiểm tra 2: Có lỗi JavaScript không?
Xem tab Console có dòng màu đỏ (error) không?

### Kiểm tra 3: AJAX request có thành công không?
1. Mở tab "Network" trong Developer Tools
2. Thực hiện cập nhật
3. Tìm request đến `/admin/shipping-fees/update`
4. Xem Status Code (phải là 200)
5. Xem Response (phải có `success: true`)

### Kiểm tra 4: Modal có đúng ID không?
Mở Console và gõ:
```javascript
$('#editShippingFeeModal').length
```
Kết quả phải là: `1`

## Test thủ công Toast:

Mở Console và gõ lệnh sau để test Toast:
```javascript
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
});

Toast.fire({
    icon: 'success',
    title: 'Test Toast thành công!'
});
```

Nếu Toast hiện lên → SweetAlert2 hoạt động bình thường
Nếu Toast không hiện → SweetAlert2 chưa được load hoặc có conflict

## Giải pháp nếu vẫn không hoạt động:

### Giải pháp 1: Kiểm tra layout admin
File: `resources/views/layouts/admin.blade.php`

Đảm bảo có dòng:
```html
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

### Giải pháp 2: Load SweetAlert2 trước jQuery
Thứ tự load script phải là:
1. jQuery
2. SweetAlert2
3. Custom scripts

### Giải pháp 3: Sử dụng alert() tạm thời
Thay Toast bằng alert để test:
```javascript
success: function(response) {
    alert('Thành công: ' + response.message);
    // ... rest of code
}
```

## Liên hệ hỗ trợ:

Nếu vẫn không hoạt động, gửi cho tôi:
1. Screenshot Console (F12)
2. Screenshot Network tab
3. Response từ server
