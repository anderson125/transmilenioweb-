<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class seederX extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        
        $this->call([
            ParkingSeeder::class,
            // ParentsSeeder::class,
            BikerSeeder::class,
            BicySeeder::class,
            RolePermissionsSeed::class,
            VisitSeeder::class
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
