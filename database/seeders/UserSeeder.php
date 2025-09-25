<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['Admin','admin@example.com',1,'active'],
            ['Nguyễn An','an@example.com',2,'active'],
            ['Trần Bình','binh@example.com',2,'active'],
            ['Lê Chi','chi@example.com',2,'inactive'],
        ];
        foreach ($users as [$name,$email,$roleId,$status]) {
            User::updateOrCreate(['email'=>$email], [
                'name' => $name,
                'password' => Hash::make('password'),
                'role_id' => $roleId,
                'status' => $status,
            ]);
        }
    }
}


