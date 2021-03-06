<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Biker;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Route;
use DB;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class VigilantUserController extends Controller
{ 
    private $role;


    public function __construct(){
        if(Route::getCurrentRoute()){
            $route = Route::getCurrentRoute()->uri();
            $this->service = ( preg_match("/api\/user/",$route)) ? "app" : "web";
            $this->role = 'vigilant';
            $this->role_id = '4';
        }
    }

    public function index(){

        $users = User::role($this->role)->select('users.*', DB::raw("{$this->role_id} as role_id"), DB::raw("{$this->role_id} as prerole_id") )->get();

        return response()->json([
            "message"=>"Succesful",
            "response"=>[
                "users" => $users ,
            ]
        ],200);

        return response()->json([
            "message"=>"Succesful",
            "response"=>[
                "users" => User::role($this->role)->get(),
            ]
        ],200);
    }

    public function create(Request $request){}
    
    public function show($id){ 

        $user = User::role($this->role)->where(['id'=>$id])->select('users.*', DB::raw("{$this->role_id} as role_id"), DB::raw("{$this->role_id} as prerole_id") )->first();
        if(!$user){
            return response()->json(['message'=>'Not Found', 'response'=>['id'=>$id]],404);
        }

        return response()->json([
            "message"=>"Succesful",
            "response"=>[
                "user" => $user
            ]
        ],200);

    }

    public function store(Request $request){
        $validation = [
            "rules" => [
                'name' => 'required|min:3|max:50',
                'last_name' => 'required|min:3|max:50',
                'phone' => 'required|digits_between:7,10',
                'document' => 'required|digits_between:5,20|unique:users',
                'email' => 'required|email|min:8|max:60|unique:users',
                'password' => 'required|min:8|max:16',
                'role_id' => 'required|in:1,3,4',
                'parkings_id'=>'required|exists:parkings,id'
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener m??nimo 3 caracteres',
                'name.max' => 'El campo nombre debe tener m??ximo 50 caracteres',
                
                'last_name.required' => 'El campo apellido es requerido',
                'last_name.min' => 'El campo apellido debe tener m??nimo 3 caracteres',
                'last_name.max' => 'El campo apellido debe tener m??ximo 50 caracteres',

                'phone.required'=> 'El campo telefono es requerido',
                'phone.digits_between'=>'El campo telefono debe tener un m??nimo de 7 y un m??ximo de 10 caracteres numericos',

                'document.required'=> 'El campo documento es requerido',
                'document.unique'=> 'El documento ingresado ya existe.',
                'document.digits_between'=>'El campo documento debe tener un m??nimo de 5 y un m??ximo de 20 caracteres numericos',
                
                'email.required'=> 'El campo email es requerido',
                'email.email'=>'El campo email debe ser de tipo email',
                'email.unique'=> 'El email ingresado ya existe.',

                'password.required'=>'El campo contrase??a es requerido',
                'password.min'=>'El campo contrase??a debe tener m??nimo 8 caracteres',
                'password.max'=>'El campo contrase??a debe tener m??ximo 16 caracteres',

                'parkings_id.required'=>'El campo Bici Estaci??n es requerido',
                'parkings_id.exists'=>'El campo Bici Estaci??n no acerta ning??n registro existente',

                'role_id.required'=>'El campo rol es requerido',
                'role_id.in'=>'El campo rol permite valores Administrador(1), Vigilante(3), Consultor(4)',

            ]
        ];
        try {
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'parkings_id' => $request->parkings_id,
                'document' => $request->document,
                'password' => Hash::make($request->password),
                'service' => $this->service
            ]);

            $role = Role::find($request->role_id);                
            $user->assignRole($role->name);

            return response()->json(['message' => 'User Created'], 201);
        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }
    }

    public function update(Request $request, $id){
        
        $validation = [
            "rules" => [
                'name' => 'required|min:3|max:50',
                'last_name' => 'required|min:3|max:50',
                'phone' => 'required|digits_between:7,10',
                'email' => 'required|email|min:8|max:60',
                'password' => 'min:8|max:16',
                'active' => 'required|in:2,1',
                'role_id' => 'required|in:1,3,4',
                'parkings_id'=>'required|exists:parkings,id'
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener m??nimo 3 caracteres',
                'name.max' => 'El campo nombre debe tener m??ximo 50 caracteres',
                
                'last_name.required' => 'El campo apellido es requerido',
                'last_name.min' => 'El campo apellido debe tener m??nimo 3 caracteres',
                'last_name.max' => 'El campo apellido debe tener m??ximo 50 caracteres',

                'phone.required'=> 'El campo telefono es requerido',
                'phone.digits_between'=>'El campo telefono debe tener un m??nimo de 7 y un m??ximo de 10 caracteres numericos',

                'email.required'=> 'El campo email es requerido',
                'email.email'=>'El campo email debe ser de tipo email',
                'email.unique'=> 'El email ingresado ya existe.',

                'password.sometimes'=>'El campo contrase??a es sometimes',
                'password.min'=>'El campo contrase??a debe tener m??nimo 8 caracteres',
                'password.max'=>'El campo contrase??a debe tener m??ximo 16 caracteres',

                'active.required'=>'El campo estado es requerido',
                'active.in'=>'El campo estado recibe los valores Activo e Inactivo',
                'parkings_id.required'=>'El campo Bici Estaci??n es requerido',
                'parkings_id.exists'=>'El campo Bici Estaci??n no acerta ning??n registro existente',

                'role_id.required'=>'El campo rol es requerido',
                'role_id.in'=>'El campo rol permite valores Administrador(1), Vigilante(3), Consultor(4)',


            ]
        ];

        try {

            $user = User::role($this->role)->where(['id'=>$id])->first();
            if(!$user){
                return response()->json(['message'=>'Not Found', 'response'=>['id'=>$id]],404);
            }
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }

            $user->name = $request->input('name');
            $user->last_name = $request->input('last_name');
            $user->document = $request->input('document');
            $user->parkings_id = $request->input('parkings_id');
            $user->phone = $request->input('phone');
            $user->email = $request->input('email');
            if($request->input('password')){
                $user->password = Hash::make($request->input('password'));
            }
            $user->active = $request->input('active');
            $user->service = $this->service;

            $saving = $user->save();

            $user->removeRole($this->role);
            $role = Role::find($request->role_id);                
            $user->assignRole($role->name);

            if(!$saving){
                return response()->json([
                    "message"=>"Internal Error",
                    "response"=>[
                        "errors"=> [
                            "No se ha conseguido actualizar la informaci??n."
                        ]

                    ]
                ],500);
            }

            return response()->json(['message' => 'User Updated'], 200);

        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }


    }

    public function destroy($id){
        try {

            $user = User::role($this->role)->where(['id'=>$id])->first();
            if(!$user){
                return response()->json(['message'=>'Not Found', 'response'=>['id'=>$id]],404);
            }
            $user->delete();

            return response()->json(['message' => 'User Deleted'], 200);
        }catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }

    }

    public function getRestorePasswordCode(Request $request){

        $user = User::role($this->role)->where('email', $request->email)->first();

        if(!$user){
            return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Usuario no encontrado']]],404);
        }

        $codeRequest = VerificationCode::generate($request->email);
        $code = $codeRequest['code'];

        $currentTime = date('H:i');

        $content = "Este es el c??digo que usar?? para restablecer la contrase??a. C??digo {$code} Hora {$currentTime}";

        Biker::Notify(['phone'=>$user->phone, 'message'=>$content]);

        return response()->json(['message'=>'Success', 'response'=>['code'=>$code,'errors'=>[]]],200);

    }

    public function restorePassword(Request $request){

        $user = User::role($this->role)->where('email', $request->email)->first();
        if(!$user){
            return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Usuario no encontrado']]],404);
        }

         $validation = [
            "rules" => [
                'password' => 'required|min:8|max:16',
                'code' => 'required|exists:verification_codes,code'
            ],
            "messages" => [
                'code.required'=>'El campo c??digo de verificaci??n es requerido',
                'code.exists'=>'El c??digo de verificaci??n no ha sido encontrado o ya ha sido procesado.',
                'password.required'=>'El campo contrase??a es requerido',
                'password.min'=>'El campo contrase??a debe tener m??nimo 8 caracteres',
                'password.max'=>'El campo contrase??a debe tener m??ximo 16 caracteres',
            ]
        ];
        try {
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            
            $vef = VerificationCode::validate($request->code, $request->email);
            
            if(!$vef){
                return response()->json(['response' => ['errors'=>['El c??digo de verificaci??n no acerta las credenciales con las que fue registrado.']], 'message' => 'Bad Request'], 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['message' => 'Success', 'response'=>['errors'=>[]]], 200);
        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }

    }

}
