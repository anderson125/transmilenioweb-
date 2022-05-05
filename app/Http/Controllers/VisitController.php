<?php

namespace App\Http\Controllers;

use App\Models\Bicy;
use App\Models\Biker;
use App\Models\Visit;
use App\Models\Parking;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use DateTime;

use Illuminate\Support\Facades\Validator;

class VisitController extends Controller
{


    private $client;

    public function __construct(){
        if(Route::getCurrentRoute()){
            $route = Route::getCurrentRoute()->uri();
            $this->client = ( preg_match("/api\//",$route)) ? "app" : "web";
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = DB::table('visits')
                ->join('parkings', 'parkings.id', '=', 'visits.parkings_id')
                ->join('bikers', 'bikers.id', '=', 'visits.bikers_id')
                ->join('bicies', 'bicies.id', '=', 'visits.bicies_id')
                ->join('visit_statuses', 'visit_statuses.id', '=', 'visits.visit_statuses_id')
                ->select('visits.*',
                    DB::raw('IF(visits.duration = 0 , NULL ,visits.date_output) as date_output'),
                    DB::raw('IF(visits.duration = 0 , NULL ,SUBSTRING(visits.time_output,1,5)) as time_output'),
                    DB::raw('SUBSTRING(visits.time_input,1,5) as time_input'),
                'parkings.name as parking', DB::raw('CONCAT(bikers.name, " ", bikers.last_name) as biker'),  'visit_statuses.name as status', 'bicies.code as bicyCode')
                ->get();

            $parking = DB::table('parkings')
                ->select('parkings.name as text', 'parkings.id as value')
                ->where('parkings.active', '=', 1)
                ->get();

            $biker = DB::table('bikers')
                ->select('bikers.name as text', 'bikers.id as value')
                ->where('bikers.active', '=', 1)
                ->get();

            $status = DB::table('visit_statuses')
                ->select('visit_statuses.name as text', 'visit_statuses.id as value')
                ->where('visit_statuses.active', '=', 1)
                ->get();

            $active = [
                ["text" => "Activo", "value" => 1],
                ["text" => "Inactivo", "value" => 2],
                ["text" => "Bloqueado", "value" => 3],
            ];

            return response()->json(
                [
                    'message' => "Sucess",
                    'response' => [
                        'visits' => $data,
                        'indexes' => [
                            'status' => $status, 'biker' => $biker, 'parking' => $parking, 'active' => $active
                        ]
                    ]
                ],
                200
            );
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $currenDate = date('Y-m-d');
        $Parking = Parking::find($request->parkings_id);
        $Visits = Visit::where(['date_input'=>$currenDate, 'parkings_id'=>$request->parkings_id])->get();

        $consecutive = substr("0000". ($Visits->count() + 1),-4,4);
        $noDashDate = date('Ymd');
        $code = "{$Parking->code}{$noDashDate}{$consecutive}";

        return response()->json(['message'=>'Success', 'response'=>['data'=>['consecutive'=>$code], 'indexes'=>[], 'errors'=>[]]],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $bicyRules = ($this->client == 'web') ? 'required|exists:bicies,id' : 'required|exists:bicies,code';

         $validation = [
            "rules" => [
                'parking'   => 'required|exists:parkings,id',
                'bicy'   => $bicyRules,
                'dateInput' =>  'required|date',
                'timeInput' =>  'required|date_format:H:i',
                'status'    => 'required|exists:visit_statuses,id',
            ],
            "messages" => [
                'parking.required' => 'El campo parqueadero es requerido',
                'parking.exists'=> 'El campo parqueadero no acerta ningún registro existente',

                'bicy.required' => 'El campo bicicleta es requerido',
                'bicy.exists'=> 'El campo bicicleta no acerta ningún registro existente',

                'dateInput.required'=>'El campo fecha de ingreso es requerido',
                'dateInput.date'=>'El campo fecha de ingreso es de tipo fecha',

                'timeInput.required'=>'El campo hora de ingreso es requerido',
                'timeInput.date_format'=>'El campo hora de ingreso es de tipo hora (00:00)',

                'status.required'=>'El campo estado de la visita es requerido',
                'status.exists'=>'El campo estado de la visita no acerta ningún registro existente'

            ]
        ];

        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all() ], 'message' => 'Bad Request'], 400);
            }


            $curddate = $request->dateInput;
            $visits = Visit::where(['date_input'=>$request->dateInput, 'parkings_id'=>$request->parking])->get();
            $number = $visits->count() + 1;

            $bicy = ($this->client == 'web') ?  Bicy::find($request->bicy) : Bicy::where(['code'=>$request->bicy])->first();

            $visit = Visit::create([
                'parkings_id' => $request->parking,
                'number' => $number,
                'bikers_id' => $bicy->biker->id,
                'bicies_id' => $bicy->id,
                'duration' => 0,
                'date_input' => $request->dateInput,
                'time_input' => $request->timeInput,
                'date_output' => 0,
                'time_output' => 0,
                'visit_statuses_id' => $request->status,
                ]);

            $smsResponse = $bicy->biker->notifyBicyStorage($bicy->id,$request->parking,$visit->id);

            $visit->getCode();
            return response()->json(['message'=>'Success', 'response'=>['data'=>$visit,'errors'=>[]]],201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }
    }

    /**
     * Stores a set of resources.
     *
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Http\Response
     */
    public function massiveStorage(Request $request, $parkingId)
    {

        $validation = [
            "rules" => [
                'bicy'   => 'required|exists:bicies,code',
                'dateInput' =>  'required|date',
                'timeInput' =>  'required|date_format:H:i',
                'dateOutput' =>  'sometimes|date',
                'timeOutput' =>  'sometimes|date_format:H:i',
            ],
            "messages" => [
                'bicy.required' => 'El campo bicicleta es requerido',
                'bicy.exists'=> 'El campo bicicleta no acerta ningún registro existente',

                'dateInput.required'=>'El campo fecha de ingreso es requerido',
                'dateInput.date'=>'El campo fecha de ingreso es de tipo fecha (AAAA-MM-DD)',

                'timeInput.required'=>'El campo hora de ingreso es requerido',
                'timeInput.date_format'=>'El campo hora de ingreso es de tipo hora (00:00)',

                'dateOutput.date'=>'El campo fecha de salida es de tipo fecha (AAAA-MM-DD)',
                'timeOutput.date_format'=>'El campo hora de salida es de tipo hora (00:00)',
            ]
        ];

        try {

            if(!$request->visits){ return response()->json(['message'=>'Bad Request', 'response'=>['errors'=>['No se han recibido visitas en la petición.']]],400);}
            if(gettype($request->visits) != 'array'){ return response()->json(['message'=>'Bad Request', 'response'=>['errors'=>['El cuerpo de la petición no contempla un formato apropiado, este debe ser el de una lista de visitas.']]],400);}

            $Parking = Parking::find($parkingId);
            if(!$Parking){return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Registro de Bici Estación no encontrado']]],404);}

            $errors = [];
            $succeses = [];
            $log = [];
            foreach($request->visits as $i => $visit){

                $currentVisitErrors = [];
                $validator = Validator::make($visit, $validation['rules'], $validation['messages']);
                if ($validator->fails()) {
                    $currentVisitErrors = array_merge($currentVisitErrors , $validator->errors()->all());
                }

                $Bicy  = Bicy::where(['code'=>$visit['bicy']])->first();
                if(!count($currentVisitErrors)){

                    $prevOpenedVisit = Visit::where(['bicies_id'=>$Bicy->id, 'date_input'=>$visit['dateInput'], 'time_input'=>$visit['timeInput'], 'duration'=>'0'])->first();
                    if($prevOpenedVisit){
                        $log[] = "La visita #$i Ha sido abierta previamente";
                        if(!(array_key_exists('dateOutput',$visit) && $visit['dateOutput']) || !(array_key_exists('timeOutput',$visit) && $visit['timeOutput'])){
                            $log[] = "La visita #$i Aunque existente previamente no ha sido cerrada. Ningún proceso ha sido realizado";

                        }else{
                            $log[] = "La visita #$i abierta previamente ha sido cerrada";
                            $prevOpenedVisit->time_output = $visit['timeOutput'];
                            $prevOpenedVisit->date_output = $visit['dateOutput'];
                            $prevOpenedVisit->duration = $this->calculateDuration(
                                ['date'=>$visit['dateInput'],'time'=>$visit['timeInput']],
                                ['date'=>$visit['dateOutput'],'time'=>$visit['timeOutput']]
                            );

                            if($prevOpenedVisit->save()){
                                $Visit = $prevOpenedVisit;
                            }else{
                                $Visit = null;
                                $visit['errors'] = ['No se ha conseguido actualizar el registro, revisa la información y comparte el caso.'];
                                $errors[] = $visit;
                            }

                        }

                    }else{

                        $visits = Visit::where(['date_input'=>$visit['dateInput'], 'parkings_id'=>$parkingId])->get();
                        $number = $visits->count() + 1;
                        $visitSchema = [
                            'parkings_id' => $parkingId,
                            'number' => $number,
                            'bikers_id' => $Bicy->biker->id,
                            'bicies_id' => $Bicy->id,
                            'date_input' => $visit['dateInput'],
                            'time_input' => $visit['timeInput'],
                            'date_output' => $visit['dateInput'],
                            'time_output' => $visit['timeInput'],
                            'duration' => '0',
                            'visit_statuses_id' => 1,
                        ];

                        if(!(array_key_exists('dateOutput',$visit) && $visit['dateOutput']) || !(array_key_exists('timeOutput',$visit) && $visit['timeOutput'])){
                            $log[] = "La visita #$i Ha sido abierta pero no cerrada";

                        }else{
                            $log[] = "La visita #$i Ha sido abierta y cerrada";
                            $visitSchema['duration'] =$this->calculateDuration(
                                ['date'=>$visit['dateInput'],'time'=>$visit['timeInput']],
                                ['date'=>$visit['dateOutput'],'time'=>$visit['timeOutput']]
                            );
                            $visitSchema['date_output'] = $visit['dateOutput'];
                            $visitSchema['time_output'] = $visit['timeOutput'];
                        }

                        $Visit = Visit::create($visitSchema);
                    }

                    if($Visit){
                        $Visit->getCode();
                    }
                    $succeses[] = $Visit;

                }else{
                    $visit['errors'] = $currentVisitErrors;
                    $errors[] = $visit;
                }

            }



            return response()->json(['message'=>'Success', 'response'=>['data'=>$succeses, 'errors'=>$errors, 'log'=>$log]],201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }


    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = DB::table('visits')
            ->join('bikers', 'bikers.id', '=', 'visits.bikers_id')
            ->join('bicies', 'bicies.id', '=', 'visits.bicies_id')
            ->select('visits.*',
                DB::raw('IF(visits.duration = 0 , CURDATE() ,visits.date_output) as date_output'),
                DB::raw('IF(visits.duration = 0 , SUBSTRING(CURTIME(),1,5) ,SUBSTRING(visits.time_output,1,5)) as time_output'),
                DB::raw('SUBSTRING(visits.time_input,1,5) as time_input'),
                'bikers.document as document', 'bicies.code as bicies_code')
            ->where('visits.id', '=', $id)
            ->first();

        $bicies = Bicy::where(['bikers_id'=>$data->bikers_id])->get();

        return response()->json(['message'=>'Success','response'=>['data'=>$data, 'indexes'=>['bicies'=>$bicies], 'errors'=>[]]],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $request->request->add(['visit'=>$id]);

        $validation = [
            "rules" => [
                'visit' => 'required|exists:visits,id',
                'dateOutput' =>  'required|date',
                'timeOutput' =>  'required|date_format:H:i',
                'status'    => 'required|exists:visit_statuses,id',
            ],
            "messages" => [
                'visit.required'=>'El campo visita es requerido',
                'visit.exists'=>'El campo visita no acerta ningún registro existente',
                'dateOutput.required'=>'El campo fecha de salida es requerido',
                'dateOutput.date'=>'El campo fecha de salida es de tipo fecha',
                'timeOutput.required'=>'El campo hora de salida es requerido',
                'timeOutput.date'=>'El campo hora de salida es de tipo hora (00:00)',
                'status.required'=>'El campo estado de la visita es requerido',
                'status.exists'=>'El campo estado de la visita no acerta ningún registro existente'
            ]
        ];

        if (is_numeric($request->number)) {
            $number = $request->number + 1;
        }else{
            $number = 0;
        }

        try {
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all() ], 'message' => 'Bad Request'], 400);
            }

            $visit = Visit::find($id);
            $biker= Biker::find($visit->bikers_id);

            $sDate = "{$visit->date_input} {$visit->time_input}";
            $eDate = "{$request->dateOutput} {$request->timeOutput}:00";
            if($sDate > $eDate){
                return response()->json(['response' => ['errors'=> ["La fecha de entrada($sDate) es mayor que la fecha de salida ($eDate). La fecha de entrada no es actualizable."] ], 'message' => 'Bad Request'], 400);
            }



            $start_date = new DateTime("{$visit->date_input} {$visit->time_input}");
            $since_start = $start_date->diff(new DateTime("{$request->dateOutput} {$request->timeOutput}"));
            $duration = 0;
            $duration += $since_start->days * 24 * 60;
            $duration += $since_start->h * 60;
            $duration += $since_start->i;
            $duration *= 60;
            $duration ++; // If it's closed at the same minute

            // $visit->parkings_id = $request->parking;
            // $visit->bikers_id = $request->biker;
            // $visit->date_input = $request->dateInput;
            // $visit->time_input = $request->timeInput;
            // $visit->number = $number;
            $visit->date_output = $request->dateOutput;
            $visit->time_output = $request->timeOutput;
            $visit->duration = $duration;
            $visit->visit_statuses_id = $request->status;
            $visit->save();

            $smsResponse = $biker->notifyBicyPullOut($visit->bicies_id,$visit->parkings_id,$visit->id);

            return response()->json(['message'=>'Success', 'response'=>['errors'=>[]]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }
    }


    public function updateByBicyCode(Request $request, $id){

        $validation = [
            "rules" => [
                'dateOutput' =>  'required|date',
                'timeOutput' =>  'required|date_format:H:i',
            ],
            "messages" => [
                'dateOutput.required'=>'El campo fecha de salida es requerido',
                'dateOutput.date'=>'El campo fecha de salida es de tipo fecha',
                'timeOutput.required'=>'El campo hora de salida es requerido',
                'timeOutput.date'=>'El campo hora de salida es de tipo hora (00:00)',
            ]
        ];


        $number = (is_numeric($request->number)) ?  $request->number + 1 : 0;
        try {



            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all() ], 'message' => 'Bad Request'], 400);
            }

            $bike = Bicy::where(['code'=>$id])->first();

            if(!$bike){
                return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Registro de bicicleta no encontrado']]],404);
            }

            $activeVisits = Visit::where(['bicies_id'=>$bike->id, 'duration'=>0])->get();
            if(!$activeVisits->count()){
                return response()->json(['message'=>'Bad Request', 'response'=>['errors'=>['La bicicleta no tiene visitas activas']]],400);
            }
            $biker = $bike->biker;

            $responses = [];
            foreach($activeVisits as $visit){
                $sDate = "{$visit->date_input} {$visit->time_input}";
                $eDate = "{$request->dateOutput} {$request->timeOutput}:00";
                if($sDate > $eDate){
                    return response()->json(['response' => ['errors'=> ["La fecha de entrada($sDate) es mayor que la fecha de salida ($eDate). La fecha de entrada no es actualizable."] ], 'message' => 'Bad Request'], 400);
                }



                $start_date = new DateTime("{$visit->date_input} {$visit->time_input}");
                $since_start = $start_date->diff(new DateTime("{$request->dateOutput} {$request->timeOutput}"));
                $duration = 0;
                $duration += $since_start->days * 24 * 60;
                $duration += $since_start->h * 60;
                $duration += $since_start->i;
                $duration *= 60;
                if($duration == 0){ $duration++; } // If it's closed at the same minute

                $visit->date_output = $request->dateOutput;
                $visit->time_output = $request->timeOutput;
                $visit->duration = $duration;
                if($visit->save()){
                    $responses[]= $visit;
                }

                $smsResponse = $biker->notifyBicyPullOut($visit->bicies_id,$visit->parkings_id,$visit->id);
            }



            return response()->json(['message'=>'Success', 'response'=>['data'=>[$responses],'errors'=>[]]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Visit  $visit
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Visit::find($id);
            if(!$data){
                return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Registro de visita no encontrado']]],404);
            }
            $data->delete();
            return response()->json(['message'=>'Success', 'response'=>['errors'=>[]]],200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }
    }


    /**
     * Calculates visit duration
     *
     * @param Array $entry
     * @param Array $exit
     * @return Int $duration
     */
    private function calculateDuration($entry = [], $exit =[]){
        $start_date = new DateTime("{$entry['date']} {$entry['time']}");
        $since_start = $start_date->diff(new DateTime("{$exit['date']} {$exit['time']}"));
        $duration = 0;
        $duration += $since_start->days * 24 * 60;
        $duration += $since_start->h * 60;
        $duration += $since_start->i;
        $duration *= 60;
        $duration ++;
        return $duration;
    }
}
