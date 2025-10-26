<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;

class ImportProductImages extends Command
{
    protected $signature = 'products:import-images';
    protected $description = 'Upload product images from external URLs to Cloudinary and update database paths';

    public function handle()
    {
        // Lấy các ảnh có link HTTP (ảnh cũ lấy từ ngoài)
        $images = ProductImage::where('image_url', 'like', 'http%')->get();

        if ($images->isEmpty()) {
            $this->warn("⚠️ Không có ảnh nào cần import.");
            return;
        }

        // Khởi tạo cấu hình Cloudinary từ config
        $cloudName = config('cloudinary.cloud.cloud_name');
        $apiKey    = config('cloudinary.cloud.api_key');
        $apiSecret = config('cloudinary.cloud.api_secret');

        if (!$cloudName || !$apiKey || !$apiSecret) {
            $this->error('❌ Thiếu cấu hình Cloudinary. Vui lòng thiết lập CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET trong .env');
            return 1;
        }

        $cfg = Configuration::instance([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key'    => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true,
            ],
        ]);

        $this->info("🔄 Bắt đầu upload {$images->count()} ảnh lên Cloudinary...");

        $cloudDisk = Storage::disk('cloudinary');

        foreach ($images as $image) {
            $url = $image->image_url;
            $this->line("➡️ Đang tải: {$url}");

            try {
                // Tải nội dung ảnh từ URL
                $contents = @file_get_contents($url);
                if ($contents === false) {
                    $this->error("❌ Không tải được: {$url}");
                    continue;
                }

                // Lấy extension (nếu không có thì mặc định jpg)
                $path = parse_url($url, PHP_URL_PATH);
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $extension = 'jpg';
                }

                // Tạo tên file ngẫu nhiên
                $filename = 'products/' . time() . '_' . uniqid() . '.' . $extension;

                // Kiểm tra nội dung có phải ảnh hợp lệ
                if (@getimagesizefromstring($contents) === false) {
                    $this->error("❌ Nội dung không phải ảnh hợp lệ: {$url}");
                    continue;
                }

                // Upload lên Cloudinary bằng core SDK UploadApi để nhận secure URL
                $temp = tmpfile();
                fwrite($temp, $contents);
                $meta = stream_get_meta_data($temp);
                $filePath = $meta['uri'];
                $uploader = new UploadApi();
                $uploadResult = $uploader->upload($filePath, [
                    'folder' => 'products',
                    'public_id' => pathinfo($filename, PATHINFO_FILENAME),
                    'resource_type' => 'image',
                ]);
                $cloudinaryUrl = $uploadResult['secure_url'] ?? ($uploadResult['url'] ?? null);
                fclose($temp);

                // Cập nhật DB với URL Cloudinary
                $image->image_url = $cloudinaryUrl;
                $image->save();

                $this->info("✅ Upload thành công: {$cloudinaryUrl}");
            } catch (\Exception $e) {
                $this->error("❌ Lỗi khi xử lý {$url}: " . $e->getMessage());
            }
        }

        $this->info("🎉 Hoàn tất upload tất cả ảnh lên Cloudinary!");
    }
}
