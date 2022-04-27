<?php

namespace Database\Seeders;

use App\Models\Parking;
use Illuminate\Database\Seeder;

class ParkingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Parking::truncate();

        $data = Parking::create([
            'name'  => 'Parqueadero Prueba',
            'code'  => 'PP',
            'capacity'  => 1000,
            'type_parkings_id'  => 1,
            'stations_id'  => 1,
        ]);

        $data = Parking::create([
            'name'  => 'San Mateo',
            'code'  => 'SMT',
            'capacity'  => 300,
            'type_parkings_id'  => 1,
            'stations_id'  => 1,
        ]);

        $data = Parking::create([
            'name'  => 'Sur',
            'code'  => 'SUR',
            'capacity'  => 500,
            'type_parkings_id'  => 1,
            'stations_id'  => 1,
        ]);
        $data = Parking::create([
            'name'  => 'Américas',
            'code'  => 'AMR',
            'capacity'  => 700,
            'type_parkings_id'  => 1,
            'stations_id'  => 1,
        ]);
    }
}
