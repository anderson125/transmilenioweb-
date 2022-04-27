<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
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
            TypeParkingSeeder::class,
            StationSeeder::class,
            ParkingSeeder::class,
            TypeDocumentSeeder::class,
            GenderSeeder::class,
            JobSeeder::class,
            NeighborhoodSeeder::class,
            ParentsSeeder::class,
            ParametersSeeder::class,
            LevelSeeder::class,
            BikerSeeder::class,
            TypeBicySeeder::class,
            BicyStatusSeeder::class,
            BicySeeder::class,
            VisitStatusSeeder::class,
            RolePermissionsSeed::class,
            UserAppSeeder::class,
            VisitSeeder::class
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
