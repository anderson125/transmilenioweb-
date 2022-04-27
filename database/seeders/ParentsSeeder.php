<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Parents;

class ParentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Parents::Create([
            'name'=>'Papa Roach',
            'active'=>1,
            'phone'=>'3219192092',
            'document'=>'9988776655',
            'users_id'=>1,
        ]);
    }
}
