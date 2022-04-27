<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Inventory;

class InventoryBicy extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_id', 'bicies_id'];

    public function inventory(){
        return $this->belongsTo(inventory::class);
    }
}
