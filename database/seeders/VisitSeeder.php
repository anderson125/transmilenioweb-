<?php

namespace Database\Seeders;

use App\Models\Visit;
use Illuminate\Database\Seeder;

class VisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Visit::truncate();
        $data = Visit::create([
            'parkings_id'  => '1',
            'number'  => '1',
            'bikers_id'  => '3',
            'bicies_id'  => '1',
            'duration'  => 60,
            'date_input'  => '2020-12-20',
            'time_input'  => '02:02:00',
            'date_output'  => '2020-12-20',
            'time_output'  => '02:03:00',
            //'new'  => 'Ninguna',
            'visit_statuses_id'  => '1',
        ]);

        $data = Visit::create([
            'parkings_id'  => '2',
            'number'  => '1',
            'bikers_id'  => '3',
            'bicies_id'  => '2',
            'duration'  => 86400,
            'date_input'  => '2020-12-20',
            'time_input'  => '02:02:00',
            'date_output'  => '2020-12-21',
            'time_output'  => '02:02:00',
            //'new'  => 'Epa',
            'visit_statuses_id'  => '1',
        ]);


        $data = Visit::create([
            'parkings_id'  => '3',
            'number'  => '1',
            'bikers_id'  => '2',
            'bicies_id'  => '3',
            'duration'  => 60,
            'date_input'  => '2020-12-20',
            'time_input'  => '02:02:00',
            'date_output'  => '2020-12-20',
            'time_output'  => '02:03:00',
            //'new'  => 'Ninguna',
            'visit_statuses_id'  => '1',
        ]);
    }
}
