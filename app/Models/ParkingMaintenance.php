<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Parking;

class ParkingMaintenance extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'description',
        'parkings_id',
        'finished'
    ];

    public function parking(){
        return $this->hasOne(Parking::class,'id','parkings_id');
    }

}
