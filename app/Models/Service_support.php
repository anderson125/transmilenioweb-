<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service_support extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'parkings_id',
        'title',
        'description',
        'status',
    ];
}
