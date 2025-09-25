<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id');
        $products = Product::where('active', true)->get();
        if ($products->count() < 5) return;

        foreach (range(1,5) as $n) {
            $userId = $users->random();
            $lines = $products->random(rand(2,4))->map(function($p){
                $qty = rand(1, max(1, min(3, $p->stock)));
                return ['product'=>$p,'qty'=>$qty,'price'=>$p->price];
            });
            $total = $lines->sum(fn($l)=>$l['qty']*$l['price']);
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $total,
                'status' => rand(0,1) ? 'pending' : 'paid',
                'payment_method' => rand(0,1) ? 'COD' : 'MOMO',
                'customer_name' => 'Khách '.$n,
                'customer_email' => 'khach'.$n.'@mail.com',
                'customer_phone' => '09'.rand(10,99).rand(1000000,9999999),
                'customer_address' => 'HCM, Việt Nam',
            ]);
            foreach ($lines as $l) {
                $maxQty = max(1, min($l['qty'], $l['product']->stock));
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $l['product']->id,
                    'quantity' => $maxQty,
                    'price' => $l['price'],
                ]);
                $l['product']->decrement('stock', $maxQty);
            }
        }
    }
}


