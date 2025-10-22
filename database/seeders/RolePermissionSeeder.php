<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Xóa cache quyền để tránh lỗi trùng
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==============================
        // 🔹 NHÓM QUYỀN: SẢN PHẨM
        // ==============================
        $productPermissions = [
            'view products',       // xem danh sách sản phẩm
            'create products',     // thêm sản phẩm
            'edit products',       // sửa sản phẩm
            'delete products',     // xóa sản phẩm
        ];
        $categoryPermissions = [
            'view categories',
            'create categories',
            'edit categories',
            'delete categories'
        ];
        $promotionPermissions = [
            'view promotions',
            'create promotions',
            'edit promotions',
            'delete promotions'
        ];
        $shippingPermissions = [
            'view shipping fees',
            'create shipping fees',
            'edit shipping fees',
            'delete shipping fees'
        ];
        $orderPermissions = [
            'view orders',
            'view order detail',
            'change status orders',
            'print orders'
        ];

        // ==============================
        // 🔹 NHÓM QUYỀN: KHÁCH HÀNG
        // ==============================
        $customerPermissions = [
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'lock/unlock customers',
        ];
        $reviewPermissions = [
            'view reviews',
            'hide reviews',
            'delete reviews'
        ];

        // ==============================
        // 🔹 NHÓM QUYỀN: NHÂN VIÊN
        // ==============================
        $staffPermissions = [
            'view staffs',
            'create staffs',
            'edit staffs',
            'delete staffs',
            'lock/unlock staffs',
        ];

        // ==============================
        // 🔹 NHÓM QUYỀN: THỐNG KÊ
        // ==============================
        $statPermissions = [
            'view reports', // xem thống kê & báo cáo
        ];

        // ==============================
        // 🔹 NHÓM QUYỀN: HỆ THỐNG
        // ==============================
        $systemPermissions = [
            'manage settings', // cài đặt hệ thống
            'manage roles',    // phân quyền
            'manage permissions',
        ];

        // Tạo tất cả quyền (nếu chưa tồn tại)
        $allPermissions = array_merge(
            $productPermissions,
            $categoryPermissions,
            $promotionPermissions,
            $shippingPermissions,
            $orderPermissions,
            $customerPermissions,
            $reviewPermissions,
            $staffPermissions,
            $statPermissions,
            $systemPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ==============================
        // 🔹 TẠO VAI TRÒ
        // ==============================
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $employee = Role::firstOrCreate(['name' => 'Nhân viên']);
        $customer = Role::firstOrCreate(['name' => 'Khách hàng']);

        // ==============================
        // 🔹 PHÂN QUYỀN CHO TỪNG VAI TRÒ
        // ==============================

        // Admin có toàn quyền
        $admin->givePermissionTo(Permission::all());

        // Nhân viên chỉ được phép quản lý danh mục, sản phẩm, đơn hàng, khuyến mãi, khách hàng cơ bản
        $employee->syncPermissions([
            'view products',
            'view categories',
            'view reviews',
            'view reports'
        ]);

        // Làm mới cache quyền (bắt buộc sau khi thay đổi role/permission)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}   
