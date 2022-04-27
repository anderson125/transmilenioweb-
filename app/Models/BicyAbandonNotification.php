<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BicyAbandonNotification extends Model
{
    use HasFactory;

    protected $fillable = ['bicies_id','active','ready_for_dispatching'];
}
