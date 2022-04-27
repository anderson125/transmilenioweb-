<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

use App\Models\Biker;

class VerificationCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'verification'];

    public static function expiredCodes(){
        $currentDate = date('Y-m-d H:i:s');
        $limitDate = date('Y-m-d H:i:s', strtotime($currentDate. " - 1 hours"));
        VerificationCode::where('created_at','<',$limitDate)->delete();
    }

    public static function generate($verification = null){
        VerificationCode::expiredCodes();
        $code = 100000;
        while(true){
            $code = rand(243657,978563);
            if(!count(VerificationCode::where(['code'=>$code])->get()->toArray())){
                break;
            }
        }
        $vefCode = VerificationCode::create(['code'=>$code, 'verification'=>$verification]);
        return ['code'=>$vefCode->code];

    }

    public static function validate($code,$verification = null){
        VerificationCode::expiredCodes();
        $query = ($verification)? ['code'=>$code, 'verification'=>$verification] : ['code'=>$code];

        $vefCode = VerificationCode::where($query)->first();

        if($vefCode){
            $vefCode->delete();
            return true;
            return response()->json(['message'=>'Success', 'response'=>['errors'=>[]]],200);
        }else{
            return false;
            throw new Exception('El código de verificación es inválido o ya ha sido procesado.');
            return response()->json(['message'=>'Bad Request', 'response'=>['errors'=>[]]],400);
        }
        return $vefCode;

    }


}
