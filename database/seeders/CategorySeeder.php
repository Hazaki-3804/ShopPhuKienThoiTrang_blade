<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = ['Túi xách', 'Mũ', 'Kính', 'Vòng tay', 'Dây chuyền'];
        foreach ($names as $name) {
            Category::firstOrCreate([
                'slug' => Str::slug($name)
            ], [
                'name' => $name,
            ]);
        }
    }
}


