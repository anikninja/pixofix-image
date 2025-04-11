<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Anik Ninja',
            'email' => 'anik89bd@gmail.com',
        ])->assignRole( RolesEnum::Admin->value );

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ])->assignRole( RolesEnum::Admin->value );
        
        User::factory()
            ->count(3)
            ->sequence(fn ($sequence) => [
            'name' => 'Employee ' . ($sequence->index + 1),
            'email' => 'employee' . ($sequence->index + 1) . '@example.com',
            ])
            ->create()
            ->each(function ($user) {
            $user->assignRole( RolesEnum::Employee->value );
            });

        User::factory()
            ->count(5)
            ->create()
            ->each(function ($user) {
                $user->assignRole( RolesEnum::User->value );
            });
        
    }
}
