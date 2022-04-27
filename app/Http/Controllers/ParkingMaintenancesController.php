<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ParkingMaintenance;
use App\Models\Biker;


class ParkingMaintenancesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ParkingMaintenance::all();

        return response()->json([
            'message'=>'Success',
            'response'=>[
                'data'=>$data,
                'errors'=>[]
            ]
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $validation = [
            "rules" => [
                'start_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_date' => 'required|date',
                'end_time' => 'required|date_format:H:i',
                'parkings_id' => 'required|exists:parkings,id',
                'finished' => 'sometimes|in:0,1'
            ],
            "messages" => [
                'start_date.required' => 'El campo fecha de inicio es requerido',
                'start_date.date' => 'El campo fecha de inicio es de tipo fecha',
                'start_time.required' => 'El campo hora de inicio es requerido',
                'start_time.date_format' => 'El campo hora de inicio es de tipo hora (00:00)',

                'end_date.required' => 'El campo fecha de finalización es requerido',
                'start_date.date' => 'El campo fecha de finalización es de tipo fecha',
                'end_time.required' => 'El campo hora de finalización es requerido',
                'start_time.date_format' => 'El campo hora de finalización es de tipo hora (00:00)',

                'parkings_id.required' => 'El campo Bici Estación es requerido',
                'parkings_id.exists' => 'El campo Bici Estación  no acerta ningún registro existente',

                'finished.in'=>'El campo mantenimiento finalizado recibe los valores Sí y No'

            ]
        ];

        
        try {
            Log::alert($request->all());
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            
            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            } 

            $parkingMaintenance = ParkingMaintenance::create($request->all());
            Biker::notifyParkingUnderMaintenance($parkingMaintenance->id);
            return response()->json(['message' => 'Success', 'response' => ["id" => $parkingMaintenance['id'], "errors" => []]], 201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // avoid the record's parking to be updated
        $request->request->remove('parkings_id');
        
        $request->request->add(['parkingMaintenance_id'=>$id]);

        $validation = [
            "rules" => [
                // 'start_date' => 'required|date',
                // 'start_time' => 'required|date_format:H:i',
                // 'end_date' => 'required|date',
                // 'end_time' => 'required|date_format:H:i',
                'finished' => 'sometimes|in:0,1',
                'parkingMaintenance_id' => 'required|exists:parking_maintenances,id'
            ],
            "messages" => [
                'start_date.required' => 'El campo fecha de inicio es requerido',
                'start_date.date' => 'El campo fecha de inicio es de tipo fecha',
                'start_time.required' => 'El campo hora de inicio es requerido',
                'start_time.date_format' => 'El campo hora de inicio es de tipo hora (00:00)',

                'end_date.required' => 'El campo fecha de finalización es requerido',
                'start_date.date' => 'El campo fecha de finalización es de tipo fecha',
                'end_time.required' => 'El campo hora de finalización es requerido',
                'start_time.date_format' => 'El campo hora de finalización es de tipo hora (00:00)',

                'parkingMaintenance_id.required' => 'El campo mantenimiento es requerido',
                'parkingMaintenance_id.exists' => 'El campo mantenimiento  no acerta ningún registro existente',

                'finished.in'=>'El campo mantenimiento finalizado recibe los valores Sí y No'

            ]
        ];

        
        try {
            Log::alert($request->all());
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            
            if ($validator->fails()) {
                return response()->json(['message' => 'Bad Request','response' => ['errors' => $validator->errors()->all()]], 400);
            } 

            $parkingMaintenance = ParkingMaintenance::find($id);
            if($parkingMaintenance->finished == 0 && $request->finished == 1){
                $parkingMaintenance->end_date = date('Y-m-d');
                $parkingMaintenance->end_time = date('H:i');
                $parkingMaintenance->finished = 1;
                $parkingMaintenance->save();
                $smsResponses = Biker::notifyParkingAvailable($parkingMaintenance->id);
            }else{
                return response()->json(['message' => 'Bad Request','response' => ['errors' => 'La única actualización permitida es la finalización del mantenimiento']], 400);
            }

            return response()->json(['message' => 'Success', 'response' => ["errors" => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
