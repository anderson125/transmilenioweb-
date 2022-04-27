<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = Job::create([
            'name'  => 'N/A',
            'users_id'  => 1
        ]);
        $data = Job::create([
            'name'  => 'Desarrollador',
            'users_id'  => 1
        ]);
    }
}
