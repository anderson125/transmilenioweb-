<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\InventoryBicy;
use App\Models\Bicy;

class Inventory extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function bicies(){

        return $this->hasManyThrough(
            Bicy::class,
            InventoryBicy::class,
            'inventory_id',
            'id',
            'id',
            'bicies_id',

        );
    }
}
