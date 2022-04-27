<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = Gender::create([
            'code'  => 'M',
            'name'  => 'Masculino',
            'users_id'  => 1
        ]);
    }
}
