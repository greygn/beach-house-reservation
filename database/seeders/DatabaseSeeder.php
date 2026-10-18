<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Status::SYSTEM as $name) {
            Status::firstOrCreate(['name' => $name]);
        }

        $admin = User::firstOrNew(['email' => 'admin@example.com']);
        $admin->fill(['name' => 'Admin', 'phone' => '+10000000000', 'password' => 'password']);
        $admin->is_admin = true;
        $admin->save();
    }
}
