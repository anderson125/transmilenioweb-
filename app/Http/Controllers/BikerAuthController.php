<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\Parents;
use App\Models\BikerAuth;
use App\Models\VerificationCode;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class BikerAuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = DB::table('biker_auths')
                ->join('parents', 'parents.id', '=', 'biker_auths.parents_id')
                ->join('bikers', 'bikers.id', '=', 'biker_auths.biker_young')
                ->select('biker_auths.*', 
                        'parents.name as parent_name', 'parents.document as parent_document', 'parents.phone as parent_phone',
                        'bikers.id as young', 'bikers.document as young_document', 'bikers.name as young_name', 'bikers.last_name as young_last_name')
                ->get();

            $type = DB::table('parents')
                ->select('parents.*', 'parents.name as text', 'parents.document as value')
                ->where('parents.active', '=', 1)
                ->get();
            return response()->json(
            [
              'message'=>'Success',
                'response'=>[
                    'data' => $data, 
                    'indexes' =>[
                        'parents' => $type
                    ] 
                ]
            ], 200 
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
        try {

            $validation = [
                "rules" => [
                    'parent' => 'required|exists:parents,id',
                    'confirmation' => 'required|exists:verification_codes,code',
                    'young' =>  'required|exists:bikers,id',                
                ],
                "messages" => [
                    'confirmation.exists'=> 'El código de verificación no ha sido encontrado o ya ha sido procesado.',
                    'confirmation.required'=> 'El campo usuario es requerido',
                    'parent.required'=> 'El campo representante es requerido',
                    'parent.exists'=> 'El campo representante no acerta ningún registro existente',
                    'young.required'=> 'El campo ciclista es requerido',
                    'young.exists'=> 'El campo ciclista no acerta ningún registro existente',
                ]
            ];

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['message' => 'Bad Request',  'response' => ['errors' => $validator->errors()->all()]], 400);
            }
          
            
            
            VerificationCode::validate($request->confirmation);
            $biker = Biker::find($request->young);
            $parent = Parents::find($request->parent);
            $user = $request->user();

            $biker->auth = 1;
            $biker->save();
            
            Biker::notifySuccesfullAuthorization(['biker'=>$biker, 'parent'=>$parent]);
            BikerAuth::create([
                'biker_young' => $biker->id,
                'parents_id' => $request->parent,
                'users_id' => $user->id,
            ]);
            
            return response()->json(['message' => 'Success', 'response' => ['errors' => []]], 201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BikerAuth  $bikerAuth
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BikerAuth  $bikerAuth
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = DB::table('biker_auths')
            ->join('parents', 'parents.id', '=', 'biker_auths.parents_id')
            ->join('bikers as young', 'young.id', '=', 'biker_auths.biker_young')
            ->join('bikers as user', 'user.id', '=', 'biker_auths.users_id')
            ->select('biker_auths.id', 'parents.document as parents_id', 'young.document as biker_young', 'user.document as users_id')
            ->where('biker_auths.id', '=', $id)
            ->first();


        if (!$data) {
            return response()->json(['message' => "Not Found", 'response' => ['errors' => ["Autorización no encontrada."]]], 404);
        }

        return response()->json(['message' => 'Success', 'response' => [
            'youngs' => $data,
            'errors' => []
        ]], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BikerAuth  $bikerAuth
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {

            // if (!$user = $request->user()) {
            //     return response()->json(['message' => 'Unauthorized', 'response' => ["errors" => ['No se ha conseguido identificar el usuario que realiza la petición.']]], 401);
            // }
            $bikerUser = Biker::where('document', $request->users_id)->first();
            if (!$bikerUser) {
                return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['Representante No encontrado']]], 400);
            }

            $bikerYoung = Biker::where('document', $request->biker_young)->first();
            if (!$bikerYoung) {
                return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['Menor No encontrado']]], 400);
            }

            // $request->request->add(['user' => $user->id]);

            $validation = [
                "rules" => [
                    'id' => 'required|exists:biker_auths,id',
                    'parents_id' => 'required|exists:parents,id',
                    'biker_young' =>  'required',
                ],
                "messages" => [
                    'id.required' => 'El campo autorización es requerido',
                    'id.exists' => 'El campo autorización no acerta ningún registro existente',
                    'parents_id.required' => 'El campo parentesco es requerido',
                    'parents_id.exists' => 'El campo parentesco no acerta ningún registro existente',
                    'biker_young.required' => 'El campo menor es requerido',
                    'biker_young.exists' => 'El campo menor no acerta ningún registro existente',
                ]
            ];

            // Log::alert($request->all());

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['message' => 'Bad Request',  'response' => ['errors' => $validator->errors()->all()]], 400);
            }
            $user = $request->user();

            Schema::disableForeignKeyConstraints();
            $data = BikerAuth::find($request->id);
            $data->parents_id = $request->parents_id;
            $data->biker_young = $bikerYoung->id;
            $data->users_id = $user->id;
            $data->save();
            Schema::enableForeignKeyConstraints();
            return response()->json(['message' => 'Success', 'response' => ['errors' => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BikerAuth  $bikerAuth
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $request->request->add(['id' => $id]);

            $validation = [
                "rules" => [
                    'id' => 'required|exists:biker_auths,id',
                ],
                "messages" => [
                    'id.required' => 'El campo autorización es requerido',
                    'id.exists' => 'El campo autorización no acerta ningún registro existente'
                ]
            ];
            Log::alert($request->all());

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['message' => 'Bad Request',  'response' => ['errors' => $validator->errors()->all()]], 400);
            }

            $data = BikerAuth::find($id);

            $biker = Biker::find($data->biker_young);
            $biker->auth = 2;
            $biker->save();

            $data->delete();
            
            return response()->json(['message' => 'Success',  'response'=>['errors'=>[]]],200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }
}
