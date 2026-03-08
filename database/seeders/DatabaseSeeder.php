<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class DatabaseSeeder extends Seeder
{
    use HasRoles;
    
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed geographical data first
        $this->call([   
            RoleSeeder::class,       
            CountriesTableSeeder::class,            
            CountySeeder::class,
            SubCountySeeder::class,            
            StatesTableSeeder::class,
            CitiesTableChunkOneSeeder::class,
            CitiesTableChunkTwoSeeder::class,
            CitiesTableChunkThreeSeeder::class,
            CitiesTableChunkFourSeeder::class,
            CitiesTableChunkFiveSeeder::class,
        ]);

    }
}
