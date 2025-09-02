<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       User::create([
            'name'          => 'Admin',
            'email'         => 'super@gmail.com',
            'password'      => Hash::make('secret1234'),
            'role'          => 'super_admin', 
            'country_code'  => '+91',
            'phone'         => '9999999999',
            'dob'           => '1990-01-01',
            'doj'           => now(),
            'latitude'      => '26.1445',
            'longitude'     => '91.7362',
            'profile_photo' => null,
            'is_active'     => true,
            'device_info'   => 'Seeder Device',
            'fcm_token'     => 'sample_token',
        ]);
    }
}
