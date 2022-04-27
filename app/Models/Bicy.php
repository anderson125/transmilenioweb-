<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Biker;
use App\Models\BicyAbandonNotification;

class Bicy extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'parkings_id',
        'bikers_id',
        'brand',
        'color',
        'tires',
        'description',
        'type_bicies_id',
        'serial',
        'code',
        'active',
        'url_image_back',
        'url_image_side',
        'url_image_front',
        'id_image_back',
        'id_image_side',
        'id_image_front',
    ];

    public function biker()
    {
        return $this->hasOne(Biker::class, 'id', 'bikers_id');
    }
    public function abandonNotification()
    {
        return $this->hasMany(BicyAbandonNotification::class, 'bicies_id', 'id');
    }
}
