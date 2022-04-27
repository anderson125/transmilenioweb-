<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = Station::create([
            'name'  => 'Estacion de parqueadero',
            'users_id'  => 1
        ]);
    }
}
