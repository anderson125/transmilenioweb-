<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use DB;
use App\Models\User;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $_roles = Role::where('id','!=',2)->get();
        $roles = array(); 
        // $roles->permissions;
        foreach($_roles as $role){
            $role->permissions;
            $roles[] = $role;
        }
            
        return response()->json(['message'=>'Success','response'=>[
            'data'=>$roles
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
        $validation = [
            "rules" => [
                'name' => 'required|unique:roles',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.unique' => 'El nombre ingresado ya existe',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $role = Role::create(['name'=>$request->name, 'guard_name'=>'web']);

            return response()->json(['message' => 'Success', 'response' => ["data" => $role, "errors" => []],], 201);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }


    public function edit($id){
        $role = Role::find($id);
        $permissions = Permission::all();
        $role->permissions;

        $role->nonPermissions = array_filter($permissions, function($permission){
            
        });
    }

    /**
     * Asign role to user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function asignToUser(Request $request,$id){
        
        $request->request->add(['role_id'=>$id]);

        $validation = [
            "rules" => [
                'role_id' => 'required|exists:roles,id',
                'user_id' => 'required|exists:users,id|unique:model_has_roles,model_id',
            ],
            "messages" => [
                'role_id.required' => 'El campo rol es requerido',
                'role_id.exists' => 'El campo rol no acerta ningún registro existente.',
                'user_id.required' => 'El campo usuario es requerido',
                'user_id.exists' => 'El campo usuario no acerta ningún registro existente.',
                'user_id.unique' => 'El usuario ya tiene un rol asignado.',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            // $exists = DB::table('model_has_roles')->where(['role_id'=>$request->role_id,'model_id'=>$request->user_id])->get();
            // if($exists->count()){
            //     return response()->json(['response' => ['errors' => ['El usurio ya pertenece al rol especificado.']], 'message' => 'Bad Request'], 400);
            // }

            $Role = Role::find($id);
            $User = User::find($request->user_id);            
            $User->assignRole($Role->name);

            return  response()->json(['message' => 'Success', 'response' => ["errors" => []],], 201);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Revoke role to user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function revokeToUser(Request $request,$id){
        
        $request->request->add(['role_id'=>$id]);

        $validation = [
            "rules" => [
                'role_id' => 'required|exists:roles,id',
                'user_id' => 'required|exists:users,id',
            ],
            "messages" => [
                'role_id.required' => 'El campo rol es requerido',
                'role_id.exists' => 'El campo rol no acerta ningún registro existente.',
                'user_id.required' => 'El campo usuario es requerido',
                'user_id.exists' => 'El campo usuario no acerta ningún registro existente.',
                'user_id.unique' => 'El usuario ya tiene un rol asignado.',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $exists = DB::table('model_has_roles')->where(['role_id'=>$request->role_id,'model_id'=>$request->user_id])->get();
            if(!$exists->count()){
                return response()->json(['response' => ['errors' => ['El usuario no pertenece al rol especificado.']], 'message' => 'Bad Request'], 400);
            }

            DB::table('model_has_roles')->where(['role_id'=>$request->role_id,'model_id'=>$request->user_id])->delete();

            return  response()->json(['message' => 'Success', 'response' => ["errors" => []],], 201);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }


    /**
     * Asign permission to role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authRoleTo(Request $request,$id){

        $request->request->add(['role_id'=>$id]);

        $validation = [
            "rules" => [
                'role_id' => 'required|exists:roles,id',
                'permission_id' => 'required|exists:permissions,id',
            ],
            "messages" => [
                'role_id.required' => 'El campo rol es requerido',
                'role_id.exists' => 'El campo rol no acerta ningún registro existente.',
                'permission_id.required' => 'El campo permiso es requerido',
                'permission_id.exists' => 'El campo permiso no acerta ningún registro existente.',
            ]
        ];


        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $Role = Role::find($id);
            if(gettype($request->permission_id) != 'array'){
                $request->permission_id = [$request->permission_id];
            }
            foreach($request->permission_id as $permission_id){
                $Permission = Permission::find($permission_id);
                $Role->givePermissionTo($Permission->name);
            }


            return  response()->json(['message' => 'Success', 'response' => ["errors" => []],], 201);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Asign permission to role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function unauthorizeRoleTo(Request $request,$id){

        $request->request->add(['role_id'=>$id]);

         $validation = [
            "rules" => [
                'role_id' => 'required|exists:roles,id',
                'permission_id' => 'required|exists:permissions,id',
            ],
            "messages" => [
                'role_id.required' => 'El campo rol es requerido',
                'role_id.exists' => 'El campo rol no acerta ningún registro existente.',
                'permission_id.required' => 'El campo permiso es requerido',
                'permission_id.exists' => 'El campo permiso no acerta ningún registro existente.',
            ]
        ];

        
        try {
            
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            
            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            
            $exists = DB::table('role_has_permissions')->where(['role_id'=>$request->role_id,'permission_id'=>$request->permission_id])->get();
            if(!$exists->count()){
                return response()->json(['response' => ['errors' => ['El rol no tiene el permiso especificado.']], 'message' => 'Bad Request'], 400);
            }

            DB::table('role_has_permissions')->where(['role_id'=>$request->role_id,'permission_id'=>$request->permission_id])->delete();

            return  response()->json(['message' => 'Success', 'response' => ["errors" => []],], 201);

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
        $Role = Role::find($id);
        if(!$Role){
            return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['No se ha encontrado el rol especificada.']]], 404);
        }

        $roleUsers = $Role->users;
        if($roleUsers->count()){
            return  response()->json(['message' => 'Success', 'response' => ["errors" => ['El rol tiene usuarios asignados.']],], 400);
        }

        $rolePermissions = $Role->permissions;

        foreach($rolePermissions as $permission){
            $permission->delete();
        }

        $Role->delete();

        return  response()->json(['message' => 'Success', 'response' => ["errors" => []],], 200);
    }
}
