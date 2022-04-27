<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use DB;
use App\Models\Bicy;
use App\Models\Parking;
use App\Models\ParkingMaintenance;
use App\Models\BicyAbandonNotification;
use App\Models\VerificationCode;
use Exception;
use DateTime;

class Biker extends Model
{
    use HasFactory;
    protected $guarded = [];

    
    public function bicies(){
        return $this->hasMany(Bicy::class,'bikers_id','id');
    }

    
    public function notifyBicyStorage($bicy_id, $parking_id, $visit_id)
    {
        $parking = Parking::find($parking_id);
        $visit = Visit::find($visit_id);
        $currentTime = date('H:i');
        $content = "Usted ha sido registrado con éxito en las Bici Estaciones del Sistema TransMilenio, Bici Estación  {$parking->name}. Recuerde que usted es el único responsable por la seguridad de su bicicleta asegúrela bien Hora {$currentTime}" ;
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
    }

    public function notifyBicyPullOut($bicy_id, $parking_id, $visit_id = 1)
    {
        $parking = Parking::find($parking_id);
        $visit = Visit::find($visit_id);
        $bici = Bicy::find($bicy_id);

        $abandonNotification = $bici->abandonNotification()->where('active','1')->first();
        if($abandonNotification){
            $abandonNotification->active = '0';
            $abandonNotification->save();
        }
        $currentTime = date('H:i');
        $content = "Usted ha salido de la Bici Estación {$parking->name} Hora {$currentTime}";
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);

    }

    public function sendRawTextMessage($message){
        $resp = Biker::Notify(['phone'=>$this->phone, 'message'=>$message]);
        return $resp;
    }

    public static function getParentVerificationCode($opt){

        $codeRequest = VerificationCode::generate($opt['phone']);
        $code = $codeRequest['code'];

        extract($opt);

        $currentTime = date('H:i');
        $content = "Su código de confirmación de registro en la Bici Estación de TransMilenio es: {$code}. Hora {$currentTime}";

        $content = "Señor(a) {$parent->name}, con el siguiente código podrá autorizar al menor {$biker->name} {$biker->last_name} para que se registre en el sistema de Bici Estación de TransMilenio y los datos del menor sean tratados de conformidad con la política de tratamiento que podrá consultar en la página www.transmilenio.gov.co. 
Código: {$code} Hora: {$currentTime}";

        Biker::Notify(['phone'=>$phone, 'message'=>$content]);
        return response()->json(['message'=>'Success','response'=>['code'=>$code]]);
        
    }

    public static function notifySuccesfullAuthorization($opt){
        $currentTime = date('H:i');
        extract($opt); // $parent , $biker
        $phone = $parent->phone;
        $content = "Señor(a) {$parent->name}, el menor {$biker->name} {$biker->last_name} ha quedado autorizado para que se registre con sus datos personales en el Sistema de Bici Estación de TransMilenio.
Hora: {$currentTime}";
        Biker::Notify(['phone'=>$phone, 'message'=>$content]);
        return response()->json(['message'=>'Success','response'=>[]]);
    }


    public static function getVerificationCode($phone){

        $codeRequest = VerificationCode::generate($phone);
        $code = $codeRequest['code'];

        $currentTime = date('H:i');
        $content = "Su código de confirmación de registro en la Bici Estación de TransMilenio es: {$code}. Hora {$currentTime}";
        Biker::Notify(['phone'=>$phone, 'message'=>$content]);
        return response()->json(['message'=>'Success','response'=>['code'=>$code]]);

    }

    public function notifySignup($parkings_id)
    {
        $currentTime = date('H:i');
        $parking = Parking::find($parkings_id);

        $content = "Usted ha sido registrado con éxito en las Bici Estaciones del Sistema TransMilenio, Bici Estación {$parking->name}. Hora {$currentTime}"
             ."\n  Los datos personales suministrados tienen como finalidad realizar el registro para el uso e ingreso a las Bici Estaciones, como titular podrá ejercer sus derechos a través del canal: habeasdata@transmilenio.gov.co así como consultar la Política de Datos personales en la página web de la Entidad https://www.transmilenio.gov.co/publicaciones/149179/politica-de-tratamiento-de-datos-habeas-data/." ;
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
    }

    public function notifyBicySignin($bicy_id){

        $currentTime = date('H:i');

        $bicy= Bicy::where('bicies.id',$bicy_id)
        ->join('parkings','parkings_id','parkings.id')
        ->select('bicies.*','parkings.name as parking')
        ->first();
        
        $content = "Usted ha registrado con éxito la bicicleta {$bicy->brand} color {$bicy->color} en el Sistema de Bici Estaciones de TransMilenio, Bici Estación {$bicy->parking} código de Registro {$bicy->code}$bicy_id. Hora {$currentTime}";
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
    }
    public function notifyBicyUpdate($bicy_id){
        $bicy= Bicy::where('bicies.id',$bicy_id)
        ->join('parkings','parkings_id','parkings.id')
        ->select('bicies.*','parkings.name as parking')
        ->first();
        $content = "Usted ha actualizado con éxito la bicicleta {$bicy->brand} color {$bicy->color} en el Sistema de Bici Estaciones de TransMilenio, Bici Estación {$bicy->parking}. Código de Registro {$bicy->code}{$bicy->id}.";
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
    }
    

    public static function notifyParkingUnderMaintenance($parkingMaintenance_id, $filters = []){

        $bikers =(count($filters)) ? Biker::where($filers)->get() : Biker::all();

        $parkingMaintenance = ParkingMaintenance::find($parkingMaintenance_id);
        if(!$parkingMaintenance){
            throw new Exception('Parking Maintenance not found');
        }

        $parking = $parkingMaintenance->parking;
        if(!$parking){
            throw new Exception('Parking not found');
        }

        $responses = [];
        foreach($bikers as $biker){
            $responses[] = $biker->_notifyParkingUnderMaintenance($parkingMaintenance);
        }
        return [$responses, $bikers];
    }
    public function _notifyParkingUnderMaintenance($parkingMaintenance){
     
        $startDatetime = DateTime::createFromFormat('Y-m-d', $parkingMaintenance->start_date);
        $startDayName = $this->translateDayName($startDatetime->format('D'));
        $startMonthName = $this->translateMonthName($startDatetime->format('M'));
        $startDay = $startDatetime->format('d');

        $endDatetime = DateTime::createFromFormat('Y-m-d', $parkingMaintenance->end_date);
        $endDayName = $this->translateDayName($endDatetime->format('D'));
        $endMonthName = $this->translateMonthName($endDatetime->format('M'));
        $endDay = $endDatetime->format('d');
        
        $creationDatetime = DateTime::createFromFormat('Y-m-d H:i:s', $parkingMaintenance->created_at);
        $creationTime = $creationDatetime->format('H:i');

        $content = "Estimado usuario(a), por labores de mantenimiento, la Bici Estación {$parkingMaintenance->parking->name} estará cerrado desde las " . substr($parkingMaintenance->start_time,0,5) . " del día {$startDayName} {$startDay} de {$startMonthName} hasta las " . substr($parkingMaintenance->end_time,0,5) . " del día {$endDayName} {$endDay} de {$endMonthName}. Disculpe los inconvenientes que pueda ocasionar. Hora {$creationTime}";
        return Biker::notify(['phone'=>$this->phone, 'message'=>$content]);
    }


    public static function notifyParkingAvailable($parkingMaintenance_id, $filters = []){

        $bikers =(count($filters)) ? Biker::where($filers)->get() : Biker::all();

        $parkingMaintenance = ParkingMaintenance::find($parkingMaintenance_id);
        if(!$parkingMaintenance){
            throw new Exception('Parking Maintenance not found');
        }

        $parking = $parkingMaintenance->parking;
        if(!$parking){
            throw new Exception('Parking not found');
        }

        $responses = [];
        foreach($bikers as $biker){
            $responses[] = $biker->_notifyParkingAvailable($parkingMaintenance);
        }
        return [$responses, $bikers];

    }    
    public function _notifyParkingAvailable($parkingMaintenance){
        $currentTime = date('H:i');
        $content = "Estimado usuario(a), la Bici Estación {$parkingMaintenance->parking->name} ya está disponible para su uso. Hora {$currentTime}";
        return Biker::notify(['phone'=>$this->phone, 'message'=>$content]);
    }


    public static function notifyBeingBlocked($ids = []){

        // avoid sending block message to everyone by mistake
        if(!count($ids)){
            return false;
        }
        // return "bikers.id in (" . implode(", ", $ids) . ")";

        $bikers = Biker::whereIn('id', $ids)->get();
        $responses = [];
        foreach($bikers as $biker){
           $responses[] = $biker->_notifyBeingBlocked();
        }
        return [$responses, $bikers];

        
    }
    public function _notifyBeingBlocked(){
        $currentTime = date('H:i');
        $content = "Estimado usuario(a), teniendo en cuenta que la última vez que hizo uso de la Bici Estación del sistema fue hace más de dos años, su usuario ha sido bloqueado. Hora {$currentTime}";
        $this->active = 3;
        $this->save();
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
    }
    public function unblockAndNotify(){
        $currentTime = date('H:i');
        $content = "Estimado usuario(a) el sistema de la Bici Estación de TransMilenio ya está disponible para su uso. Hora {$currentTime}";

        $this->active = 1;
        $this->save();
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
    }

    public function notifyBikeExpiration($bici_id, $parking_id){
        $currentTime = date('H:i');
        $parking = Parking::find($parking_id);

        $content = "Estimado usuario(a), su bicicleta cumplió más de 30 días continuos dentro de la Bici Estación {$parking->name}. Debe retirarla dentro de los siguientes 7 días calendario. Hora {$currentTime}.";
        $notification = BicyAbandonNotification::create(['bicies_id'=>$bici_id, 'active'=>"1"]);
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
        return $content;
    }
    public function notifyBikeAbandoning($bici_id, $parking_id){
        $currentTime = date('H:i');
        $parking = Parking::find($parking_id);
        $bici = Bicy::find($bici_id);

        $bici->active = 3;
        $bici->save();
        $notification = $bici->abandonNotification()->where('active','1')->first();
        $notification->ready_for_dispatching = '1';
        $notification->active = '0';
        $notification->save();

        $content = "Estimado usuario(a), cumplido el plazo de 7 días calendario, su bicicleta  continúa en la Bici Estación {$parking->name}, por lo cual se procederá a gestionar su declaración de abandono ante el ICBF. Hora {$currentTime}.";
        return Biker::Notify(['phone'=> $this->phone, 'message'=>$content]);
        return $content;
    }

    /**
     * Normalized function for sms sending.
     *
     * @return array
     */
    public static function Notify($opt = []){
        /* SMS Altiria provider */

        $request = curl_init();

        $headers = array( "Content-Type: application/x-www-form-urlencoded;charset=UTF-8" );
        $payload = [
            'cmd'    => 'sendsms',
            'login'  => 'andersonlopez284@gmail.com',
            'passwd' => 'cfh5crdf',
            'dest'   => '57' . $opt['phone'],
            'concat'   => 'true',
            'msg'    => $opt['message'],
            'encoding'=>'Unicode',
        ];
        curl_setopt_array($request, array(
            CURLOPT_URL => 'http://www.altiria.net/api/http',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
            ),
        ));

        $response = curl_exec($request);

        return $response;

    }

    private function translateDayName($day){
        switch(strToLower($day)){
            case 'mon': return 'lunes'; break;
            case 'tue': return 'martes'; break;
            case 'wed': return 'miércoles'; break;
            case 'thu': return 'jueves'; break;
            case 'fri': return 'viernes'; break;
            case 'sat': return 'sábado'; break;
            case 'sun': return 'domingo'; break;
        }
    }

    private function translateMonthName($month){
        switch(strToLower($month)){
            case 'jan': return 'enero'; break;
            case 'feb': return 'febrero'; break;
            case 'may': return 'mayo'; break;
            case 'apr': return 'abril'; break;
            case 'mar': return 'marzo'; break;
            case 'jun': return 'junio'; break;
            case 'jul': return 'julio'; break;
            case 'aug': return 'agosto'; break;
            case 'sep': return 'septiembre'; break;
            case 'oct': return 'octubre'; break;
            case 'nov': return 'noviembre'; break;
            case 'dec': return 'diciembre'; break;
        }
    }


}
