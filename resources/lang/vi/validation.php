<?php

return [
    'accepted' => ':attribute phải được chấp nhận.',
    'active_url' => ':attribute không phải là URL hợp lệ.',
    'after' => ':attribute phải sau :date.',
    'alpha' => ':attribute chỉ được chứa chữ cái.',
    'required' => ':attribute không được để trống.',
    'email' => ':attribute phải là địa chỉ email hợp lệ.',
    'regex' => ':attribute chỉ được chứa chữ cái và khoảng trắng.   ',
    'max' => [
        'string' => ':attribute không được dài quá :max ký tự.',
    ],
    'min' => [
        'string' => ':attribute phải ít nhất :min ký tự.',
    ],
    'confirmed' => ':attribute xác nhận không trùng khớp.',
    'unique' => ':attribute đã tồn tại.',
    // thêm các rule khác nếu cần
    'attributes' => [
        'username' => 'Tên đăng nhập',
        'name' => 'Họ tên',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'phone' => 'Số điện thoại',
        'address' => 'Địa chỉ',
        'password_confirmation' => 'Xác nhận mật khẩu',
        'province' => 'Tỉnh/Thành phố',
        'ward' => 'Xã/Phường',
    ],
];
