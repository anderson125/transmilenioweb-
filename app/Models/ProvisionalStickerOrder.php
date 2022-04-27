<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvisionalStickerOrder extends Model
{
    use HasFactory;
    protected $fillable = [ 'parkings_id', 'quantity', 'printed', 'initial_consecutive', 'final_consecutive', 'misc' ];
}
