<?php

namespace Database\Seeders;

use App\Models\VisitStatus;
use Illuminate\Database\Seeder;

class VisitStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = VisitStatus::create([
            'name'  => 'Sin Novedad',
            'users_id'  => 1
        ]);
        $data = VisitStatus::create([
            'name'  => 'Atipica',
            'description'  => 'si pasa menos de cinco minutos entre el cierre de una visita existente y la apertura  de una visita nueva; queda como atípica la visita cerrada.',
            'users_id'  => 1
        ]);
        $data = VisitStatus::create([
            'name'  => 'Tipica',
            'description'  => 'si pasa menos de cinco minutos entre la apertura y cierre de una visita, esta visita que como atípica',
            'users_id'  => 1
        ]);
    }
}
