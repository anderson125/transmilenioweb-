<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Visit;
use App\Models\Parking;
use App\Models\Bicy;

class Visit extends Model
{
    use HasFactory;
    protected $guarded = [];

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
