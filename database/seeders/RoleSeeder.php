<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Rolları yarat (əgər yoxdursa)
        $roles = ['trainer', 'admin', 'superadmin'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // İstifadəçini tap
        $user = User::where('email', 'nadir@example.com')->first();

        if ($user) {
            // admin rolunu tap
            $adminRole = Role::where('name', 'admin')->first();

            // təyin et (əgər təyin edilməyibsə)
            if (!$user->roles->contains($adminRole)) {
                $user->roles()->attach($adminRole);
            }
        }
    }
}
