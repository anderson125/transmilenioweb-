<?php

namespace Database\Seeders;

use App\Models\Service_support;
use Illuminate\Database\Seeder;

class ServiceSupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Service_support::truncate();

        $data = Service_support::create([
            'parkings_id' => '1',
            'title' => 'Robo a mano armada',
            'description' => 'Mano me robaron',
            'status' => 0,
        ]);

        $data = Service_support::create([
            'parkings_id' => '4',
            'title' => 'Novedad de bicicleta',
            'description' => 'bicicleta',
            'status' => 1,
        ]);

        $data = Service_support::create([
            'parkings_id' => '2',
            'title' => 'Aparecio la bicicleta',
            'description' => 'Aparecio la cicla mano, no estaba muerto andaba de parranda',
            'status' => 2,
        ]);

    }
}
