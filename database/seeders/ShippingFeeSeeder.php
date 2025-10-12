<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingFee;

class ShippingFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu có
        ShippingFee::truncate();

        $shippingFees = [
            // NỘI THÀNH VĨNH LONG (0-10km)
            [
                'name' => 'Miễn phí ship nội thành - Đơn từ 300k',
                'area_type' => 'local',
                'min_distance' => 0,
                'max_distance' => 10,
                'min_order_value' => 300000,
                'base_fee' => 0,
                'per_km_fee' => 0,
                'max_fee' => null,
                'is_free_shipping' => true,
                'priority' => 100,
                'status' => true,
                'description' => 'Miễn phí vận chuyển cho đơn hàng từ 300k trong nội thành Vĩnh Long'
            ],
            [
                'name' => 'Hỗ trợ ship 20k - Đơn từ 300k nội thành',
                'area_type' => 'local',
                'min_distance' => 0,
                'max_distance' => 10,
                'min_order_value' => 300000,
                'base_fee' => 10000,
                'per_km_fee' => 0,
                'max_fee' => 20000,
                'is_free_shipping' => false,
                'priority' => 90,
                'status' => false, // Tắt vì đã có miễn phí
                'description' => 'Phí ship chỉ 10k, tối đa 20k cho đơn từ 300k trong nội thành (Dự phòng)'
            ],
            [
                'name' => 'Phí ship nội thành chuẩn',
                'area_type' => 'local',
                'min_distance' => 0,
                'max_distance' => 10,
                'min_order_value' => 0,
                'base_fee' => 30000,
                'per_km_fee' => 0,
                'max_fee' => null,
                'is_free_shipping' => false,
                'priority' => 50,
                'status' => true,
                'description' => 'Phí ship cố định 30k cho nội thành Vĩnh Long (đơn dưới 300k)'
            ],

            // LÂN CẬN (10-30km)
            [
                'name' => 'Hỗ trợ ship 30k - Đơn từ 500k lân cận',
                'area_type' => 'nearby',
                'min_distance' => 10,
                'max_distance' => 30,
                'min_order_value' => 500000,
                'base_fee' => 20000,
                'per_km_fee' => 1000,
                'max_fee' => 50000,
                'is_free_shipping' => false,
                'priority' => 80,
                'status' => true,
                'description' => 'Hỗ trợ ship: 20k + 1k/km, tối đa 50k cho đơn từ 500k khu vực lân cận'
            ],
            [
                'name' => 'Phí ship lân cận chuẩn',
                'area_type' => 'nearby',
                'min_distance' => 10,
                'max_distance' => 30,
                'min_order_value' => 0,
                'base_fee' => 40000,
                'per_km_fee' => 2000,
                'max_fee' => 100000,
                'is_free_shipping' => false,
                'priority' => 40,
                'status' => true,
                'description' => 'Phí ship 40k + 2k/km cho khu vực lân cận (10-30km)'
            ],

            // TOÀN QUỐC (>30km)
            [
                'name' => 'Phí ship toàn quốc',
                'area_type' => 'nationwide',
                'min_distance' => 30,
                'max_distance' => null,
                'min_order_value' => 99000,
                'base_fee' => 50000,
                'per_km_fee' => 0,
                'max_fee' => null,
                'is_free_shipping' => false,
                'priority' => 30,
                'status' => true,
                'description' => 'Giao hàng toàn quốc cho đơn từ 99k, phí cố định 50k'
            ],
        ];

        foreach ($shippingFees as $fee) {
            ShippingFee::create($fee);
        }
    }
}
