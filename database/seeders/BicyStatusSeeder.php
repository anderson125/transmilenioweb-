<?php

namespace Database\Seeders;

use App\Models\BicyStatus;
use Illuminate\Database\Seeder;

class BicyStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = BicyStatus::create([
            'name'  => 'Activo',
            'active' => 1,
            'users_id'  => 1
        ]);

        $data = BicyStatus::create([
            'name'  => 'Inactivo',
            'active' => 2,
            'users_id'  => 1
        ]);
        $data = BicyStatus::create([
            'name'  => 'Bloqueado',
            'active' => 3,
            'users_id'  => 1
        ]);
    }
}
