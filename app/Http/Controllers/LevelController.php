<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Level::all());
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
                'code' => 'required|min:1|max:10|unique:genders',
                'name' => 'required|min:6|max:100',
                'active' => 'required|in:1,2',
            ],
            "messages" => [
                'code.required'=> 'El campo código es requerido',
                'code.min' => 'El campo código debe tener mínimo 1 caracteres',
                'code.max' => 'El campo código debe tener máximo 10 caracteres',
                'code.unique' => 'El código ingresado ya existe',
                
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 6 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                'active.required'=> 'El campo estado es requerido',
                'active.in'=>'El campo estado recibe los valores Activo e Inactivo',
            ]
        ];
        try {
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
                return response()->json(['response' => 'Error al Guardar', 'status' => 2]);
            }

            Level::create([
                'code' => $request->code,
                'name' => $request->name,
                'active' => $request->active,
                'users_id' => 1
            ]);
            return response()->json(['message' => 'Success', 'response' => ["errors"=>[] ]],201);
            return response()->json(['response' => 'Guardado Exitosamente', 'status' => 1]);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => ['errors'=>[$th->getMessage()]], 'message' => 'Internal Error'], 500);
            return response()->json(['response' => 'Error al Guardar', 'status' => 2]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(Level::whereId($id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
         try {
            $data = Level::find($request->id);

            if(!$data){
                return response()->json(['message'=>'Not Found', 'response'=>['id'=>$request->id]],404);
                return response()->json(['response' => 'Error al Actualizar', 'status' => 2]);
            }
            
            if(strtolower($data->code) == strtolower($request->input('code'))){
                $codeRules = 'required|min:1|max:10';
            }else{
                $codeRules = 'required|min:1|max:10|unique:levels';
            }

            $validation = [
                "rules" => [
                    'code' => $codeRules,
                    'name' => 'required|min:6|max:100',
                    'active' => 'required|in:1,2',
                ],
                "messages" => [
                    'code.required'=> 'El campo código es requerido',
                    'code.min' => 'El campo código debe tener mínimo 1 caracter',
                    'code.max' => 'El campo código debe tener máximo 10 caracteres',
                    'code.unique' => 'El código ingresado ya existe',
                    
                    'name.required' => 'El campo nombre es requerido',
                    'name.min' => 'El campo nombre debe tener mínimo 6 caracteres',
                    'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                    'active.required'=> 'El campo estado es requerido',
                    'active.in'=>'El campo estado recibe los valores Activo e Inactivo',
                ]
            ];
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
                return response()->json(['response' => 'Error al Guardar', 'status' => 2]);
            }

            $data->code = $request->code;
            $data->name = $request->name;
            $data->active = $request->active;
            $data->save();
            return response()->json(['message' => 'Success', 'response' => ["errors"=>[] ]],200);
            return response()->json(['response' => 'Actualizado Exitosamente', 'status' => 1]);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => ['errors'=>[$th->getMessage()]], 'message' => 'Internal Error'], 500);
            return response()->json(['response' => 'Error al Actualizar', 'status' => 2]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Level::find($id);
            $data->delete();
            return response()->json(['response' => 'Eliminado Exitosamente', 'status' => 1]);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => 'Error al Eliminar', 'status' => 2]);
        }
    }
}
