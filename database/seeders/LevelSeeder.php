<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = Level::create([
            'code'  => '1',
            'name'  => 'Estrato 1',
            'users_id'  => 1
        ]);
    }
}
