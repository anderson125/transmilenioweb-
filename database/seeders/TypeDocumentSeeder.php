<?php

namespace Database\Seeders;

use App\Models\TypeDocument;
use Illuminate\Database\Seeder;

class TypeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = TypeDocument::create([
            'code'  => 'CC',
            'name'  => 'Cédula de Ciudadanía',
            'users_id'  => 1
        ]);
        $data = TypeDocument::create([
            'code'  => 'TI',
            'name'  => 'Tarjeta de Identidad',
            'users_id'  => 1
        ]);
    }
}
