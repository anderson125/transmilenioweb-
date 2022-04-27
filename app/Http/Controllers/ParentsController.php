<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Biker;

class ParentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Parents::all());
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
                'document' => 'required|unique:parents,document',
                'phone' => 'required|min:7|max:12',
                'active' => 'required|in:1,2',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 6 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 100 caracteres',
                'phone.required' => 'El campo teléfono es requerido',
                'phone.min' => 'El campo teléfono debe tener mínimo 7 caracteres',
                'phone.max' => 'El campo teléfono debe tener máximo 12 caracteres',
                'document.required' => 'El campo documento es requerido',
                'document.unique' => 'El documento ingresado ya existe',
                'active.required' => 'El campo estado es requerido',
                'active.in' => 'El campo estado recibe los valores Activo e Inactivo',
            ]
        ];
        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            Parents::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'document' => $request->document,
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
     * @param  \App\Models\Parents  $parents
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Parents  $parents
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(Parents::whereId($id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Parents  $parents
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = false)
    {
        if($id){
            $request->request->add(['id'=>$id]);
        }
        try {
            $data = Parents::find($request->id);
            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $request->id]], 404);
            }
            $validation = [
                "rules" => [
                    'name' => 'required|min:4|max:100',
                    'active' => 'required|in:1,2',
                    'phone' => 'required|min:7|max:12',
                    'document' => 'required|unique:parents,document',
                ],
                "messages" => [
                    'name.required' => 'El campo nombre es requerido',
                    'name.min' => 'El campo nombre debe tener mínimo 6 caracteres',
                    'name.max' => 'El campo nombre debe tener máximo 100 caracteres',
                    'document.required' => 'El campo documento es requerido',
                    'document.unique' => 'El documento ingresado ya existe',
                    'phone.required' => 'El campo teléfono es requerido',
                    'phone.min' => 'El campo teléfono debe tener mínimo 7 caracteres',
                    'phone.max' => 'El campo teléfono debe tener máximo 12 caracteres',
                    'active.required' => 'El campo estado es requerido',
                    'active.in' => 'El campo estado recibe los valores Activo e Inactivo',
                ]
            ];

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $data->name = $request->name;
            $data->document = $request->document;
            $data->phone = $request->phone;
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
     * @param  \App\Models\Parents  $parents
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = Parents::find($id);
            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $id]], 404);
            }
            $data->delete();
            return response()->json(['message' => 'User Deleted'], 200);
        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => $ex->getMessage()]], 500);
        }
    }



    public function getParentVerificationCode(Request $request, $id ){

        //$phone,$message
        $request->request->add(['parent_id'=> $id]);

        $validation = [
            "rules" => [
                'parent_id' => 'required|exists:parents,id',
                'biker_id' => 'required|exists:bikers,id',
            ],
            "messages" => [
                'parent_id.required' => 'El campo acudiente es requerido',
                'parent_id.exists' => 'El campo acudiente no acerta ningún registro existente',
                'biker_id.required' => 'El campo ciclista es requerido',
                'biker_id.exists' => 'El campo ciclista no acerta ningún registro existente',
            ]
        ];
        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            $Parent = Parents::find($id);
            $Biker  = Biker::find($request->biker_id);
            return Biker::getParentVerificationCode(['phone'=>$Parent->phone, 'parent'=>$Parent, 'biker'=>$Biker]);
            return response()->json(['message' => 'Success', 'response' => ["errors" => []]], 201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => ['errors' => [$th->getMessage()]], 'message' => 'Internal Error'], 500);
        }

    }
}
