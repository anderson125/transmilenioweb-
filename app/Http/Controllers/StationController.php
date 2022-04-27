<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Station::all());
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
                'name' => 'required|min:6|max:100',
                'active' => 'required|in:1,2',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 6 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                'active.required' => 'El campo estado es requerido',
                'active.in' => 'El campo estado recibe los valores Activo e Inactivo',
            ]
        ];
        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            Station::create([
                'name' => $request->name,
                'active' => $request->active,
                'users_id' => 1
            ]);
            return response()->json(['message' => 'Success', 'response' => ["errors" => []]], 201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => ['errors' => [$th->getMessage()]], 'message' => 'Internal Error'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Station  $Station
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Station  $Station
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(Station::whereId($id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Station  $Station
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            $data = Station::find($request->id);
            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $request->id]], 404);
            }


            $validation = [
                "rules" => [
                    'name' => 'required|min:6|max:100',
                    'active' => 'required|in:1,2',
                ],
                "messages" => [
                    'name.required' => 'El campo nombre es requerido',
                    'name.min' => 'El campo nombre debe tener mínimo 6 caracteres',
                    'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                    'active.required' => 'El campo estado es requerido',
                    'active.in' => 'El campo estado recibe los valores Activo e Inactivo',
                ]
            ];

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $data->name = $request->name;
            $data->active = $request->active;
            $data->save();
            return response()->json(['message' => 'Success', 'response' => ["errors" => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => ['errors' => [$th->getMessage()]], 'message' => 'Internal Error'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Station  $Station
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Station::find($id);
            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $id]], 404);
            }
            $data->delete();
            return response()->json(['message' => 'User Deleted'], 200);
        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => $ex->getMessage()]], 500);
        }
    }
}
