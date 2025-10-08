<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ProductImage;

class ImportProductImages extends Command
{
    protected $signature = 'products:import-images';
    protected $description = 'Download product images from URLs and save them into storage/app/public/products';

    public function handle()
    {
        // Lấy các ảnh có link HTTP (ảnh cũ lấy từ ngoài)
        $images = ProductImage::where('image_url', 'like', 'http%')->get();

        if ($images->isEmpty()) {
            $this->warn("⚠️ Không có ảnh nào cần import.");
            return;
        }

        $this->info("🔄 Bắt đầu import {$images->count()} ảnh...");

        foreach ($images as $image) {
            $url = $image->image_url;
            $this->line("➡️ Đang tải: {$url}");

            try {
                // Lấy nội dung ảnh
                $contents = @file_get_contents($url);

                if ($contents === false) {
                    $this->error("❌ Không tải được: {$url}");
                    continue;
                }

                // Lấy extension từ path (bỏ query string ?v=...)
                $path = parse_url($url, PHP_URL_PATH);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                // Nếu extension không hợp lệ -> fallback jpg
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $extension = 'jpg';
                }

                // Tạo tên file random
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $storagePath = "products/{$filename}";

                // Lưu vào storage/app/public/products
                Storage::disk('public')->put($storagePath, $contents);

                // Cập nhật DB: thay link ngoài thành link storage
                $image->image_url = "storage/{$storagePath}";
                $image->save();

                $this->info("✅ Lưu thành công: storage/{$storagePath}");
            } catch (\Exception $e) {
                $this->error("❌ Lỗi khi xử lý {$url} - " . $e->getMessage());
            }
        }

        $this->info("🎉 Hoàn tất import ảnh!");
    }
}
