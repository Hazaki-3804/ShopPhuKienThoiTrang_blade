<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['id'=>1], ['name'=>'Admin','slug'=>'admin']);
        Role::updateOrCreate(['id'=>2], ['name'=>'User','slug'=>'user']);
    }
}


