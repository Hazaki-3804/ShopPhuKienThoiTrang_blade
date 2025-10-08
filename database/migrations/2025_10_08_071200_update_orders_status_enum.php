<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Sửa ENUM status từ 'confirmed' thành 'processing'
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Rollback về ENUM cũ
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
