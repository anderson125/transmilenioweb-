<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

use DB;
use App\Models\User;

class AuthsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $auths = DB::table('model_has_permissions')
            ->join('users', 'model_has_permissions.model_id', '=', 'users.id')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->select('users.id','users.document','users.name','users.email', 'permissions.name as action')
            ->get(); 
            
        return response()->json(['message'=>'Success','response'=>[
            'data'=>$auths
        ]],200);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Log::alert($request->all());
        $validation = [
            "rules" => [
                'permission_id' => 'required|exists:permissions,id',
                'user_id' =>  'required|exists:users,id',
            ],
            "messages" => [
                'permission_id.required' => 'El campo permiso es requerido',
                'permission_id.exists' => 'El campo permiso no acerta ningún registro existente',
                'user_id.required' => 'El campo usuario es requerido',
                'user_id.exists' => 'El campo usuario no acerta ningún registro existente',
            ]
        ];


        try {

                $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

                if ($validator->fails()) {
                    return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
                }
                
                $exists = DB::table('model_has_permissions')->where(['model_id'=>$request->user_id, 'permission_id'=>$request->permission_id])->get()->toArray();
                if(count($exists)){
                    return response()->json(['response' => ['errors' => ['El usuario ya posee este permiso.']], 'message' => 'Bad Request'], 400);
                }

                $user = User::find($request->user_id);
                $permission = Permission::find($request->permission_id);
                $response = $user->givePermissionTo($permission->name);

                return response()->json(['message' => 'Success', 'response' => ["user" => $response, "errors" => []],], 201);
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
    public function destroy(Request $request, $id)
    {
        $validation = [
            "rules" => [
                'permission_id' => 'required|exists:model_has_permissions,permission_id',
                'user_id' =>  'required|exists:model_has_permissions,model_id',
            ],
            "messages" => [
                'permission_id.required' => 'El campo permiso es requerido',
                'permission_id.exists' => 'El usuario no posee el permiso a eliminar.',
                'user_id.required' => 'El campo usuario es requerido',
                'user_id.exists' => 'El usuario no posee el permiso a eliminar.',
            ]
        ];

        try {
                $validator = Validator::make($request->all(),$validation['rules'], $validation['messages']);

                if ($validator->fails()) {
                    return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
                }
                
                $realtionship = DB::table('model_has_permissions')->where(['model_id'=>$request->user_id,'permission_id'=>$request->permission_id])->get();
                if(!$realtionship->count()){
                    return response()->json(['response' => ['errors' => ['No se ha encontrado la autorización especificada.']], 'message' => 'Not Found'], 404);
                }
                $deletion = DB::table('model_has_permissions')->where(['model_id'=>$request->user_id,'permission_id'=>$request->permission_id])->delete();
                
                return response()->json(['message' => 'Success', 'response' => [ "errors" => []],], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }
}
