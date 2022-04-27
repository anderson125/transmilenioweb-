<?php

namespace Database\Seeders;

use App\Models\TypeParking;
use Illuminate\Database\Seeder;

class TypeParkingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = TypeParking::create([
            'name'  => 'Tipo de parqueadero',
            'users_id'  => 1
        ]);
    }
}
