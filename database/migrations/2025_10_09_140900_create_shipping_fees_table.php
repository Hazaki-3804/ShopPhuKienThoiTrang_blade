<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipping_fees', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên quy tắc phí ship
            $table->string('area_type'); // Loại khu vực: 'local' (nội thành), 'nearby' (lân cận), 'nationwide' (toàn quốc)
            $table->decimal('min_distance', 8, 2)->default(0); // Khoảng cách tối thiểu (km)
            $table->decimal('max_distance', 8, 2)->nullable(); // Khoảng cách tối đa (km), null = không giới hạn
            $table->decimal('min_order_value', 10, 2)->default(0); // Giá trị đơn hàng tối thiểu
            $table->decimal('base_fee', 10, 2); // Phí cơ bản
            $table->decimal('per_km_fee', 10, 2)->default(0); // Phí mỗi km (nếu tính theo km)
            $table->decimal('max_fee', 10, 2)->nullable(); // Phí tối đa (nếu có)
            $table->boolean('is_free_shipping')->default(false); // Miễn phí ship
            $table->integer('priority')->default(0); // Độ ưu tiên (số càng cao càng ưu tiên)
            $table->boolean('status')->default(true); // Trạng thái kích hoạt
            $table->text('description')->nullable(); // Mô tả
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_fees');
    }
};
