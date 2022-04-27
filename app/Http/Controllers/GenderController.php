<?php

namespace App\Http\Controllers;

use App\Models\Gender;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GenderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Gender::all());
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
                'code.required' => 'El campo código es requerido',
                'code.min' => 'El campo código debe tener mínimo 1 caracteres',
                'code.max' => 'El campo código debe tener máximo 10 caracteres',
                'code.unique' => 'El código ingresado ya existe',

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

            Gender::create([
                'code' => $request->code,
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
     * @param  \App\Models\Gender  $gender
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Gender  $gender
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(Gender::whereId($id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gender  $gender
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            $data = Gender::find($request->id);

            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $request->id]], 404);
            }

            if (strtolower($data->code) == strtolower($request->input('code'))) {
                $codeRules = 'required|min:1|max:10';
            } else {
                $codeRules = 'required|min:1|max:10|unique:genders';
            }

            $validation = [
                "rules" => [
                    'code' => $codeRules,
                    'name' => 'required|min:6|max:100',
                    'active' => 'required|in:1,2',
                ],
                "messages" => [
                    'code.required' => 'El campo código es requerido',
                    'code.min' => 'El campo código debe tener mínimo 1 caracter',
                    'code.max' => 'El campo código debe tener máximo 10 caracteres',
                    'code.unique' => 'El código ingresado ya existe',

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

            $data->code = $request->code;
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
     * @param  \App\Models\Gender  $gender
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Gender::find($id);
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
