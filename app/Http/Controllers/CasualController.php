<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CasualController extends Controller
{
    //

    public function returnNotFound(Request $request){
        return response()->json(['message'=>'Not Found','response'=>['errors'=>['La ruta especificada no fue encontrada']]],404);
    }
}
