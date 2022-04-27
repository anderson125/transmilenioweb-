<?php

namespace Database\Seeders;

use App\Models\Bicy;
use Illuminate\Database\Seeder;

class BicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bicy::truncate();

        $data = Bicy::create([
            'parkings_id' => '1',
            'code' => 'GT001',
            'serial' => 'SERIALXXGT001XX',
            'bikers_id' => '1',
            'brand' => 'GW',
            'color' => 'Negro',
            'tires' => 'Lisa',
            'description' => 'Bicicleta Rafael',
            'type_bicies_id' => '1',
            'active' => '1',
            'url_image_back' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643856282/bicy/zuytlde9b4khcrbms0mq.jpg', 
            'url_image_side' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'url_image_front' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'id_image_back' => 'bicy/icxl6ebib96vgccnucbv',
            'id_image_side' => 'bicy/icxl6ebib96vgccnucbv',
            'id_image_front' => 'bicy/icxl6ebib96vgccnucbv',
            

        ]);

        $data = Bicy::create([
            'parkings_id' => '1',
            'code' => 'GT002',
            'serial' => 'SERIALXXGT002XX',
            'bikers_id' => '1',
            'brand' => 'Ryno',
            'color' => 'Azul',
            'tires' => 'Tache',
            'description' => 'Bicicleta Rafael',
            'type_bicies_id' => '2',
            'active' => '1',
            'url_image_back' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'url_image_side' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'url_image_front' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'id_image_back' => 'bicy/icxl6ebib96vgccnucbv',
            'id_image_side' => 'bicy/icxl6ebib96vgccnucbv',
            'id_image_front' => 'bicy/icxl6ebib96vgccnucbv',
        ]);

        $data = Bicy::create([
            'parkings_id' => '1',
            'code' => 'GT003',
            'serial' => 'SERIALXXGT003XX',
            'bikers_id' => '1',
            'brand' => 'Ryno',
            'color' => 'Azul',
            'tires' => 'Tache',
            'description' => 'Bicicleta Rafael',
            'type_bicies_id' => '2',
            'active' => '1',
            'url_image_back' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'url_image_side' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'url_image_front' => 'https://res.cloudinary.com/jhontt95/image/upload/v1643589196/bicy/icxl6ebib96vgccnucbv.jpg', 
            'id_image_back' => 'bicy/icxl6ebib96vgccnucbv',
            'id_image_side' => 'bicy/icxl6ebib96vgccnucbv',
            'id_image_front' => 'bicy/icxl6ebib96vgccnucbv',
        ]);
    }
}
