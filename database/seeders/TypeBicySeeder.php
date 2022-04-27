<?php

namespace Database\Seeders;

use App\Models\TypeBicy;
use Illuminate\Database\Seeder;

class TypeBicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = TypeBicy::create([
            'name'  => 'Montaña',
            'users_id'  => 1
        ]);
        $data = TypeBicy::create([
            'name'  => 'Urbana',
            'users_id'  => 1
        ]);
    }
}
