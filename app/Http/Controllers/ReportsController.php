<?php

namespace App\Http\Controllers;

use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

use App\Models\Visit;
use App\Models\Parking;
use App\Models\Bicy;

class ReportsController extends Controller
{
    public function dailyVisitsByMonths(Request $request){

        $validation = [
            "rules" => [
                'begining_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d'
            ],
            "messages" => [
                'begining_date.required' => 'El campo fecha inicio es requerido',
                'begining_date.date_format' => 'El campo fecha inicio es de tipo fecha (AAAA-MM-DD)',
                'end_date.required' => 'El campo fecha final es requerido',
                'end_date.date_format' => 'El campo fecha final es de tipo fecha (AAAA-MM-DD)',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $records = DB::table('visits')
                // ->where('parkings_id', $request->parkings_id )
                ->where('visits.date_input', '>=', $request->begining_date )
                ->where('visits.date_input','<=', $request->end_date)
                ->select(
                    'visits.parkings_id',
                    'visits.date_input',
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.date_output) as date_output')
                )
                ->get()->toArray();

            $output = array();

            foreach($records as $i => $record){

                $key = "{$record->date_input}@{$record->parkings_id}";

                if(!array_key_exists($key,$output)){
                    $output[$key] = ['in'=>0, 'out'=>0];
                }

                $output[$key]['in']++;

                if(!$record->date_output){continue;}

                if($record->date_input == $record->date_output){
                    $output[$key]['out']++;
                }else{
                    $outkey = "{$record->date_output}@{$record->parkings_id}";

                    if(!array_key_exists($outkey,$output)){
                        $output[$outkey] = ['in'=>0, 'out'=>0];
                    }

                    $output[$outkey]['out']++;
                }

            };

            // Sorting & formatting
            ksort($output);
            $data = array();
            foreach($output as $key => $value){
                $exploded = explode('@',$key);

                $value['date'] = $exploded[0];
                $value['parking_id'] = $exploded[1];

                $data[] = $value;

            }

            $parkings = Parking::all();
            return response()->json(['message'=>'success', 'response'=>['data'=>$data], 'indexes'=>['parkings'=>$parkings]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    public function visitAbandonedBicies(Request $request){

        try {

            $currentDate = date('Y-m-d H:i:s');
            $limitDate = date('Y-m-d H:i:s', strtotime($currentDate. " - 90 days"));
            $bicies = Bicy::
                where('bicies.active',1)
                ->join('visits','bicies.id','visits.bicies_id')
                ->join('bikers','bicies.bikers_id','bikers.id')
                ->join('parkings','visits.parkings_id','parkings.id')
                ->where('visits.duration','0') // If it's still inside
                ->where('visits.date_input','<=', $currentDate) // If it's still inside
                ->groupBy('bicies.id','bicies.bikers_id')
                ->select(
                    'bicies.code AS bicy_code',
                    'bikers.document AS biker_document',
                    'visits.date_input',
                    'parkings.name AS parking_name',
                    DB::raw("CONCAT( parkings.code, '-',  REPLACE(visits.date_input, '-', '' ),  IF(LENGTH(visits.number) = 1 ,  CONCAT('00',visits.number),  IF(LENGTH(visits.number) = 2 ,  CONCAT('0',visits.number) ,  visits.number  ) ) ) as visit_num"),
                    DB::raw("DATEDIFF('{$currentDate}',visits.date_input) as duration"),
                )
            ->get();

            return response()->json(['message'=>'Success', 'response'=>['data'=>$bicies, 'errors'=>[]]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    public function hourlyVisitsByDays(Request $request){

        $validation = [
            "rules" => [
                'begining_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d'
            ],
            "messages" => [
                'begining_date.required' => 'El campo fecha inicio es requerido',
                'begining_date.date_format' => 'El campo fecha inicio es de tipo fecha (AAAA-MM-DD)',
                'end_date.required' => 'El campo fecha final es requerido',
                'end_date.date_format' => 'El campo fecha final es de tipo fecha (AAAA-MM-DD)',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            if($request->parkings_id){

                $parkings = explode(',',$request->parkings_id);

                 $records = DB::table('visits')
                ->where('visits.date_input', '>=', $request->begining_date )
                ->where('visits.date_input','<=', $request->end_date)
                ->whereIn('visits.parkings_id', $parkings)
                ->select(
                    'visits.parkings_id',
                    'visits.date_input',
                    DB::raw('SUBSTRING(visits.time_input,1,2) as time_input'),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.date_output) as date_output'),
                    DB::raw('IF(visits.duration = 0 ,NULL, SUBSTRING(visits.time_output,1,2)) as time_output')
                )
                ->get()->toArray();
            } else {
                 $records = DB::table('visits')
                ->where('visits.date_input', '>=', $request->begining_date )
                ->where('visits.date_input','<=', $request->end_date)
                ->select(
                    'visits.parkings_id',
                    'visits.date_input',
                    DB::raw('SUBSTRING(visits.time_input,1,2) as time_input'),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.date_output) as date_output'),
                    DB::raw('IF(visits.duration = 0 ,NULL, SUBSTRING(visits.time_output,1,2)) as time_output')
                )
                ->get()->toArray();
            }


            $output = array();

            foreach($records as $i => $record){

                $key = "{$record->date_input}@{$record->time_input}@{$record->parkings_id}";

                if(!array_key_exists($key,$output)){
                    $output[$key] = ['in'=>0, 'out'=>0];
                }
                $output[$key]['in']++;

                if(!$record->time_output){continue;}

                if( ($record->time_input == $record->time_output) && ($record->date_input == $record->date_output) ){
                    $output[$key]['out']++;
                }else{
                    $outkey = "{$record->date_output}@{$record->time_output}@{$record->parkings_id}";

                    if(!array_key_exists($outkey,$output)){
                        $output[$outkey] = ['in'=>0, 'out'=>0];
                    }

                    $output[$outkey]['out']++;
                }

            };

            // Sorting & formating
            $data = array();
            ksort($output);
            foreach($output as $key => $value){
                $exploded = explode("@",$key);
                $value['date_input'] = $exploded[0];
                $value['time_input'] = $exploded[1] . ":00";
                $value['parking_id'] = $exploded[2];

                $data[] = $value;
            }

            $parkings = Parking::all();
            return response()->json(['message'=>'success', 'response'=>['data'=>$data], 'indexes'=>['parkings'=>$parkings]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    public function detailedBikerVisitsByMonths(Request $request){
        $validation = [
            "rules" => [
                'begining_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d'
            ],
            "messages" => [
                'begining_date.required' => 'El campo fecha inicio es requerido',
                'begining_date.date_format' => 'El campo fecha inicio es de tipo fecha (AAAA-MM-DD)',
                'end_date.required' => 'El campo fecha final es requerido',
                'end_date.date_format' => 'El campo fecha final es de tipo fecha (AAAA-MM-DD)',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            if($request->biker_document){

                $documents = explode(',',$request->biker_document);

                $records = DB::table('visits')
                ->whereIn('bikers.document', $documents )
                ->where('visits.date_input', '>=', $request->begining_date )
                ->where('visits.date_input','<=', $request->end_date)
                ->join('bikers','visits.bikers_id','bikers.id')
                ->join('parkings','visits.parkings_id','parkings.id')
                ->select(
                    'bikers.document as biker_document',
                    'parkings.name as parking',
                    DB::raw("CONCAT( parkings.code,'-',   REPLACE(visits.date_input, '-', '' ),  IF(LENGTH(visits.number) = 1 ,  CONCAT('00',visits.number),  IF(LENGTH(visits.number) = 2 ,  CONCAT('0',visits.number) ,  visits.number  ) ) ) as visit_num"),
                    'visits.date_input',
                    'visits.time_input',
                    DB::raw('SEC_TO_TIME(visits.duration) as duration'),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.date_output) as date_output'),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.time_output) as time_output')
                    )
                ->get()->toArray();

            }else{


                $records = DB::table('visits')
                ->where('visits.date_input', '>=', $request->begining_date )
                ->where('visits.date_input','<=', $request->end_date)
                ->join('bikers','visits.bikers_id','bikers.id')
                ->join('parkings','visits.parkings_id','parkings.id')
                ->select(
                    'bikers.document as biker_document',
                    'parkings.name as parking',
                    DB::raw("CONCAT( parkings.code,  REPLACE(visits.date_input, '-', '' ),  IF(LENGTH(visits.number) = 1 ,  CONCAT('00',visits.number),  IF(LENGTH(visits.number) = 2 ,  CONCAT('0',visits.number) ,  visits.number  ) ) ) as visit_num"),
                    'visits.date_input',
                    'visits.time_input',
                    DB::raw('SEC_TO_TIME(visits.duration) as duration'),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.date_output) as date_output'),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.time_output) as time_output')
                    )
                ->get()->toArray();
            }

            return response()->json(['message'=>'success', 'response'=>['data'=>$records]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    public function generalBikerVisitsByMonths(Request $request){
        $validation = [
            "rules" => [
                'begining_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d'
            ],
            "messages" => [
                'begining_date.required' => 'El campo fecha inicio es requerido',
                'begining_date.date_format' => 'El campo fecha inicio es de tipo fecha (AAAA-MM-DD)',
                'end_date.required' => 'El campo fecha final es requerido',
                'end_date.date_format' => 'El campo fecha final es de tipo fecha (AAAA-MM-DD)',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $records = DB::table('visits')
                ->where('visits.date_input', '>=', $request->begining_date )
                ->where('visits.date_input','<=', $request->end_date)
                ->join('bikers','visits.bikers_id','bikers.id')
                ->select(
                    'bikers.document as biker_document',
                    'visits.date_input',
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.date_output) as date_output')
                )
                ->get()->toArray();

            $output = array();

            foreach($records as $i => $record){

                $key = "{$record->date_input}@{$record->biker_document}";
                $key = "{$record->biker_document}";

                if(!array_key_exists($key,$output)){
                    $output[$key] = ['in'=>0, 'out'=>0];
                }

                $output[$key]['in']++;

                if(!$record->date_output){continue;}

                if($record->date_input == $record->date_output){
                    $output[$key]['out']++;
                }else{
                    $outkey = "{$record->date_output}@{$record->biker_document}";
                    $outkey = "{$record->biker_document}";

                    if(!array_key_exists($outkey,$output)){
                        $output[$outkey] = ['in'=>0, 'out'=>0];
                    }
                    $output[$outkey]['out']++;
                }
            };

            // Formating
            $data = array();
            foreach($output as $key => $value){
                $value['biker_document'] = $key;
                $data[] = $value;
            }

            $parkings = Parking::all();
            return response()->json(['message'=>'success', 'response'=>['data'=>$data], 'indexes'=>['parkings'=>$parkings]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    public function show(Request $request){

        return $this->webMapService($request);
        $validation = [
            "rules" => [
                'parkings_id' => 'sometimes|exists:parkings,id',
                'begining_date'=>'required|date_format:Y-m-d H:i',
                'end_date'=>'required|date_format:Y-m-d H:i'
            ],
            "messages" => [
                'parkings_id.exists' => 'El campo parqueadero no acerta ningún registro existente',
                'begining_date.required' => 'El campo fecha inicio es requerido',
                'begining_date.date_format' => 'El campo fecha inicio es de tipo fecha(AAAA-MM-DD HH:II)',
                'end_date.required' => 'El campo fecha final es requerido',
                'end_date.date_format' => 'El campo fecha final es de tipo fecha(AAAA-MM-DD HH:II)',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            if($request->parkings_id){
                $visits = DB::table('visits')
                ->where('parkings_id', $request->parkings_id )
                ->where('visits.created_at', '>=', $request->begining_date )
                ->where('visits.created_at','<=', $request->end_date)
                ->join('bikers','visits.bikers_id','bikers.id')
                ->join('bicies','visits.bicies_id','bicies.id')
                ->select(
                    'bikers.document as biker_document',
                    'bicies.code as bicies_code',
                    DB::raw('concat(visits.date_input, visits.time_input ) as date_input'),
                    DB::raw('IF(visits.duration = 0 ,"0000-00-00 00:00", concat(visits.date_output, visits.time_output )) as date_output'),
                    'visits.duration as visit_duration',
                    'visits.new as visit_observations'
                )
                ->get();
            }else{
                $visits = DB::table('visits')
                ->where('visits.created_at', '>=', $request->begining_date )
                ->where('visits.created_at','<=', $request->end_date)
                ->join('bikers','visits.bikers_id','bikers.id')
                ->join('bicies','visits.bicies_id','bicies.id')
                ->select(
                    'bikers.document as biker_document',
                    'bicies.code as bicies_code',
                    DB::raw('concat(visits.date_input, visits.time_input ) as date_input'),
                    DB::raw('IF(visits.duration = 0 ,"0000-00-00 00:00", concat(visits.date_output, visits.time_output )) as date_output'),
                    'visits.duration as visit_duration',
                    'visits.new as visit_observations'
                )
                ->get();

            }


            return response()->json(['message' => 'Success', 'response' => ["data" => $visits,"errors" => []],], 201);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }

    }

    public function webMapService(Request $request){
        $validation = [
            "rules" => [
                'parkings_id' => 'required|exists:parkings,id',
            ],
            "messages" => [
                'parkings_id.exists' => 'El campo parqueadero no acerta ningún registro existente',
                'parkings_id.required' => 'El campo parqueadero es requerido',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $Parking = Parking::find($request->parkings_id);
            $records = [];
            $vacancy = 'n/a';

            $currentDate = date('Y-m-d H:i:s');
            $currentTime = date('H:i:s');
            $records = DB::table('visits')
                ->where('visits.parkings_id',  $Parking->id)
                ->where( function($q){  $today = date('Y-m-d');  $q->where('visits.duration',  0)->orWhere('visits.date_input',  $today); })
                ->join('bikers','visits.bikers_id','bikers.id')
                ->join('parkings','visits.parkings_id','parkings.id')
                ->select(
                    'bikers.document as biker_document',
                    'parkings.name as parking',
                    'visits.date_input', 'visits.time_input',
                    DB::raw("CONCAT( parkings.code,'-',   REPLACE(visits.date_input, '-', '' ),  IF(LENGTH(visits.number) = 1 ,  CONCAT('00',visits.number),  IF(LENGTH(visits.number) = 2 ,  CONCAT('0',visits.number) ,  visits.number  ) ) ) as visit_num"),
                    DB::raw("
                        IF(
                            visits.duration = 0,
                            IF(
                                TIMESTAMPDIFF(SECOND, CONCAT(visits.date_input, ' ' , visits.time_input) , '{$currentDate}') < 3000000 /*Maximum timeable*/,
                                SUBSTR(SEC_TO_TIME(TIMESTAMPDIFF(SECOND, CONCAT(visits.date_input, ' ' , visits.time_input) , '{$currentDate}')),1,5),
                                CONCAT(
                                    DATEDIFF('{$currentDate}', CONCAT(visits.date_input, ' ' , visits.time_input)),
                                    'd ',
                                    SUBSTR(TIMEDIFF('{$currentTime}', visits.time_input),1,5)
                                )
                            ),
                            SUBSTR(SEC_TO_TIME(visits.duration),1,5)
                        )
                    as duration"),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.date_output) as date_output'),
                    DB::raw('IF(visits.duration = 0 ,NULL, visits.time_output) as time_output')
                )
            ->get()->toArray();


            $activeVisits = DB::table('visits')
                ->where('visits.parkings_id',  $Parking->id)
                ->where('visits.duration',  0)
            ->get();

            $vacancy = ($Parking->capacity - $activeVisits->count() );

            return response()->json(['message'=>'success', 'response'=>['data'=>$records , 'vacancy'=> $vacancy  ]],200);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }

    }

    public function pernoctas(Request $request){
        try {
            //Realizamos la consulta de la información de las visitas activas
            $pernoctas = DB::table('visits')
                ->join('parkings','visits.parkings_id','parkings.id')
                ->where('visits.date_input', '>=', $request->begining_date)
                ->where('visits.date_input','<=', $request->end_date)
                ->select('parkings.name AS parking_name',
                         'parkings.id AS parking_id',
                         'visits.date_input',
                         'visits.number',
                         'visits.bikers_id',
                         'visits.bicies_id'
                )->get()->toArray();

            $output = array(); //creamos un array
            foreach($pernoctas as $p => $pernocta){
                $key = "{$pernocta->parking_id}"; //Creamos una llave

                if(!array_key_exists($key,$output)){ //Validamos cuantas veces esta esa llave en el aray
                    $output[$key] = ['count' => 0, 'parking_id' => $pernocta->parking_id, 'parking_name' => $pernocta->parking_name];
                }
                $output[$key]['count']++;
            }

            $data = array(); //Creamos el array a enviar los datos
            foreach($output as $key => $value){
                $data[] = $value; //Mapeamos
            }

            return response()->json(['message' => 'Success', 'response' => ['data' => $data, 'errors' => [] ] ],200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }

    }

}
