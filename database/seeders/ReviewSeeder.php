<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('active', true)->get();
        foreach ($products as $p) {
            foreach (range(1, rand(2,3)) as $i) {
                $rating = rand(3,5); // 1-5, ưu tiên đẹp
                Review::create([
                    'product_id' => $p->id,
                    'user_name' => 'User '.$i,
                    'user_email' => 'user'.$i.'@mail.com',
                    'rating' => $rating,
                    'comment' => 'Sản phẩm đẹp, màu pastel phù hợp!',
                ]);
            }
        }
    }
}


