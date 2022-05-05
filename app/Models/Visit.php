<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parking;
use App\Models\Bicy;

class Visit extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
            'parkings_id',
            'parkings_id',
            'number',
            'bikers_id',
            'bikers_id',
            'bicies_id',
            'bicies_id',
            'date_input',
            'time_input',
            'date_output',
            'time_output',
            'duration',
            'visit_statuses_id',
            'visit_statuses_id',
    ];

    public function bicy(){
        return $this->hasOne(Bicy::class, 'id','bicies_id');
    }

    public function getCode(){

        $Parking = Parking::find($this->parkings_id);

        $consecutive = substr("0000". ($this->number),-4,4);
        $noDashDate = str_replace('-','',$this->date_input);
        $code = "{$Parking->code}{$noDashDate}{$consecutive}";

        $this->code = $code;
    }

}
