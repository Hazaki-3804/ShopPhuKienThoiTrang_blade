<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingFee extends Model
{
    protected $table = 'shipping_fees';

    protected $fillable = [
        'name',
        'area_type',
        'min_distance',
        'max_distance',
        'min_order_value',
        'base_fee',
        'per_km_fee',
        'max_fee',
        'is_free_shipping',
        'priority',
        'status',
        'description'
    ];

    protected $casts = [
        'min_distance' => 'float',
        'max_distance' => 'float',
        'min_order_value' => 'float',
        'base_fee' => 'float',
        'per_km_fee' => 'float',
        'max_fee' => 'float',
        'is_free_shipping' => 'boolean',
        'priority' => 'integer',
        'status' => 'boolean'
    ];

    /**
     * Tính phí ship dựa trên khoảng cách và giá trị đơn hàng
     */
    public function calculateFee($distance, $orderValue)
    {
        // Nếu miễn phí ship
        if ($this->is_free_shipping) {
            return 0;
        }

        // Tính phí cơ bản + phí theo km
        $fee = $this->base_fee + ($distance * $this->per_km_fee);

        // Áp dụng phí tối đa nếu có
        if ($this->max_fee && $fee > $this->max_fee) {
            $fee = $this->max_fee;
        }

        return $fee;
    }

    /**
     * Kiểm tra xem quy tắc có áp dụng được không
     */
    public function isApplicable($distance, $orderValue)
    {
        // Kiểm tra trạng thái
        if (!$this->status) {
            return false;
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($orderValue < $this->min_order_value) {
            return false;
        }

        // Kiểm tra khoảng cách
        if ($distance < $this->min_distance) {
            return false;
        }

        if ($this->max_distance && $distance > $this->max_distance) {
            return false;
        }

        return true;
    }

    /**
     * Get area type label
     */
    public function getAreaTypeLabel()
    {
        $labels = [
            'local' => 'Nội thành Vĩnh Long',
            'nearby' => 'Lân cận',
            'nationwide' => 'Toàn quốc'
        ];

        return $labels[$this->area_type] ?? $this->area_type;
    }
}
