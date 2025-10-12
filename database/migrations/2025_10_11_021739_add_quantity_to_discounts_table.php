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
        Schema::table('discounts', function (Blueprint $table) {
            $table->integer('quantity')->nullable()->after('status')->comment('Số lượng voucher có thể sử dụng');
            $table->integer('used_quantity')->default(0)->after('quantity')->comment('Số lượng voucher đã sử dụng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'used_quantity']);
        });
    }
};
