<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailedStickerOrder extends Model
{
    use HasFactory;
    protected $fillable = ['parkings_id', 'bicies_id', 'users_id', 'active' ];

}
