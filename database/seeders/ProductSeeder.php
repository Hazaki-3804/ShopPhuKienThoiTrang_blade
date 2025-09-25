<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id','slug');
        $data = [
            ['Túi pastel mini','Nhỏ gọn, màu pastel hiện đại', 499000, 20, 'tui-xach'],
            ['Túi tote beige','Chất vải canvas, tối giản', 399000, 15, 'tui-xach'],
            ['Mũ bucket beige','Phong cách trẻ trung', 199000, 30, 'mu'],
            ['Mũ lưỡi trai pastel','Êm nhẹ, thoáng khí', 179000, 25, 'mu'],
            ['Kính trong suốt','Gọng nhựa nhẹ, thời trang', 299000, 18, 'kinh'],
            ['Kính râm đen','Gọng kim loại', 349000, 12, 'kinh'],
            ['Vòng tay charm','Phối charm tinh tế', 259000, 22, 'vong-tay'],
            ['Vòng tay hạt đá','Gam pastel', 289000, 10, 'vong-tay'],
            ['Dây chuyền trái tim','Tinh tế, nữ tính', 319000, 14, 'day-chuyen'],
            ['Dây chuyền ngọc trai','Thanh lịch', 599000, 8, 'day-chuyen'],
            ['Túi đeo chéo mini','Dễ phối đồ', 279000, 35, 'tui-xach'],
            ['Túi shoulder pastel','Nhẹ và bền', 459000, 16, 'tui-xach'],
            ['Mũ beret','Phong cách cổ điển', 229000, 20, 'mu'],
            ['Kính bo tròn','Cá tính', 319000, 9, 'kinh'],
            ['Vòng tay dây da','Trẻ trung', 199000, 28, 'vong-tay'],
            ['Dây chuyền chữ cái','Cá nhân hoá', 349000, 11, 'day-chuyen'],
            ['Túi canvas hình học','Tối giản', 249000, 26, 'tui-xach'],
            ['Mũ len pastel','Ấm áp', 189000, 18, 'mu'],
            ['Kính mắt mèo','Thời thượng', 369000, 7, 'kinh'],
            ['Vòng tay bạc mảnh','Thanh thoát', 399000, 5, 'vong-tay'],
        ];

        foreach ($data as [$name,$desc,$price,$stock,$slug]) {
            $catId = $categories[$slug] ?? null;
            if (!$catId) continue;
            Product::create([
                'name' => $name,
                'description' => $desc,
                'price' => $price,
                'stock' => $stock,
                'image_url' => 'https://picsum.photos/seed/'.Str::slug($name).'/800/800',
                'category_id' => $catId,
                'active' => true,
            ]);
        }
    }
}


