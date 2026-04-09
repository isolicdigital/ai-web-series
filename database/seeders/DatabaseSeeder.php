<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        Plan::create([
            'name' => 'Front End',
            'slug' => 'front-end',
            'order' => 1,
            'validity_days' => 9999,
            'status' => 'active',
            'lp_id' => '6677',
        ]);

        Plan::create([
            'name' => 'Unlimited',
            'slug' => 'unlimited',
            'order' => 2,
            'validity_days' => 9999,
            'status' => 'active',
            'lp_id' => '6678',
        ]);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin'),
            'status' => 'active',
            'role' => 'admin',
            'is_agency_owner' => false,
            'agency_id' => null,
        ]);

        $plan = Plan::first();
        $admin->subscriptions()->create([
            'plan_id' => $plan->id,
            'starts_at' => now(),
            'ends_at' => now()->addYears(10),
            'status' => 'active',
        ]);
    }
}