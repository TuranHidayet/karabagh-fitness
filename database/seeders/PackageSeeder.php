<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run()
    {
        Package::insert([
            ['name' => 'Günlük', 'duration_days' => 1, 'price' => 10.00],
            ['name' => 'Həftəlik', 'duration_days' => 7, 'price' => 60.00],
            ['name' => 'Aylıq', 'duration_days' => 30, 'price' => 200.00],
            ['name' => 'İllik', 'duration_days' => 365, 'price' => 2000.00],
        ]);
    }
}
