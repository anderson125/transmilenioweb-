<?php

namespace App\Http\Controllers;

use App\Models\Parking;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ParkingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = DB::table('parkings')
                ->join('type_parkings', 'parkings.type_parkings_id', '=', 'type_parkings.id')
                ->join('stations', 'parkings.stations_id', '=', 'stations.id')
                ->select('parkings.*', 'type_parkings.name as type', 'stations.name as station')
                ->get();

            $station = DB::table('stations')
                ->select('stations.name as text', 'stations.id as value')
                ->where('stations.active', '=', 1)
                ->get();

            $type = DB::table('type_parkings')
                ->select('type_parkings.name as text', 'type_parkings.id as value')
                ->where('type_parkings.active', '=', 1)
                ->get();


            $active = [
                ["text" => "Activo", "value" => 1],
                ["text" => "Inactivo", "value" => 2],
                ["text" => "Bloqueado", "value" => 3],
            ];
        } catch (QueryException $th) {
            Log::emergency($th);
        }
        return response()->json(
            [
                'message' => "Sucess",
                'response' => [
                    'parkings' => $data,
                    'indexes' => [
                        'type' => $type, 'station' => $station, 'active' => $active
                    ]
                ]
            ],
            200
        );
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
                'name' => 'required|min:4|max:100',
                'code' =>  'required|min:2|max:10|unique:parkings',
                'capacity' =>  'required|min:1|max:30',
                'type_parkings_id' => 'required|exists:type_parkings,id',
                'stations_id' =>  'required|exists:stations,id',
                'active' =>  'required|in:1,2,3',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 4 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                'code.required' => 'El campo codigo es requerido',
                'code.unique' => 'El codigo ingresado ya existe.',
                'code.max' => 'El campo codigo debe tener máximo 2 caracteres',
                'code.min' => 'El campo codigo debe tener mínimo 10 caracteres',

                'capacity.required' => 'El campo capacidad es requerido',
                'capacity.max' => 'El campo capacidad debe tener máximo 1 caracteres',
                'capacity.min' => 'El campo capacidad debe tener mínimo 30 caracteres',

                'type_parkings_id.required' => 'El campo tipo de parqueadero es requerido',
                'type_parkings_id.exists' => 'El campo tipo de parqueadero no acerta ningún registro existente',

                'stations_id.required' => 'El campo troncal es requerido',
                'stations_id.exists' => 'El campo troncal no acerta ningún registro existente',

                'active.required' => 'El campo estado del ciclista es requerido',
                'active.in' => 'El campo estado del ciclista recibe los valores Activo, Inactivo y Bloqueado',
            ]
        ];


        try {
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            $parking = Parking::create([
                'name' => $request->name,
                'code' => $request->code,
                'capacity' => $request->capacity,
                'type_parkings_id' => $request->type_parkings_id,
                'stations_id' => $request->stations_id,
                'active' => $request->active,
            ]);
            return response()->json(['message' => 'Parking Created', 'response' => ["id" => $parking['id'], "errors" => []],], 201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Parking  $parking
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Parking  $parking
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(Parking::whereId($id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Parking  $parking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = false)
    {
        try {

            if (!$id) {
                $id = $request->input('id');
            }
            $data = Parking::find($id);
            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $id, 'errors' => ['Bici Estación No encontrado']]], 404);
            }

            $validation = [
                "rules" => [
                    'name' => 'required|min:4|max:100',
                    'code' =>  '',
                    'capacity' =>  'required|min:1|max:30',
                    'type_parkings_id' => 'required|exists:type_parkings,id',
                    'stations_id' =>  'required|exists:stations,id',
                    'active' =>  'required|in:1,2,3',
                ],
                "messages" => [
                    'name.required' => 'El campo nombre es requerido',
                    'name.min' => 'El campo nombre debe tener mínimo 4 caracteres',
                    'name.max' => 'El campo nombre debe tener máximo 100 caracteres',
    
                    'code.required' => 'El campo codigo es requerido',
                    'code.unique' => 'El codigo ingresado ya existe.',
                    'code.max' => 'El campo codigo debe tener máximo 2 caracteres',
                    'code.min' => 'El campo codigo debe tener mínimo 10 caracteres',
    
                    'capacity.required' => 'El campo capacidad es requerido',
                    'capacity.max' => 'El campo capacidad debe tener máximo 1 caracteres',
                    'capacity.min' => 'El campo capacidad debe tener mínimo 30 caracteres',
    
                    'type_parkings_id.required' => 'El campo tipo de parqueadero es requerido',
                    'type_parkings_id.exists' => 'El campo tipo de parqueadero no acerta ningún registro existente',
    
                    'stations_id.required' => 'El campo troncal es requerido',
                    'stations_id.exists' => 'El campo troncal no acerta ningún registro existente',
    
                    'active.required' => 'El campo estado del ciclista es requerido',
                    'active.in' => 'El campo estado del ciclista recibe los valores Activo, Inactivo y Bloqueado',
                ]
            ];

            
            if($data->code != $request->code){
                $validation['rules']['code'] = 'required|min:2|max:10|unique:parkings';
            }else{
                $validation['rules']['code'] = 'required|min:2|max:10';
            }

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

             if ($validator->fails()) {
                 return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
             }
     
            $data->name = $request->name;
            $data->code = $request->code;
            $data->capacity = $request->capacity;
            $data->type_parkings_id = $request->type_parkings_id;
            $data->stations_id = $request->stations_id;
            $data->active = $request->active;
            $data->save();

            return response()->json(['message' => 'Perking Updated', 'response' => ["errors" => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Parking  $parking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $data = Parking::find($id);
            if (!$data) {
                return response()->json(['message' => "Not Found", 'response' => ['errors' => ["Bici Estación no encontrado."]]], 404);
            }
            $data->delete();
            return response()->json(['message' => 'Parking Deleted',  'response' => ['errors' => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }
}
