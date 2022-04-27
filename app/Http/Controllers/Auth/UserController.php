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

class UserController extends Controller
{
    private $role;


    public function __construct(){
        if(Route::getCurrentRoute()){
            $route = Route::getCurrentRoute()->uri();
            $this->service = ( preg_match("/api\/user/",$route)) ? "app" : "web";
            $this->role = 'admin';
            $this->role_id = '1';
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
                'last_name' => 'sometimes|min:3|max:50',
                'phone' => 'required|digits_between:7,10',
                'document' => 'required|digits_between:5,20|unique:users',
                'email' => 'required|email|min:8|max:60|unique:users',
                'password' => 'required|min:8|max:16',
                'role_id' => 'required|in:1,3,4',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 3 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 50 caracteres',

                'last_name.min' => 'El campo apellido debe tener mínimo 3 caracteres',
                'last_name.max' => 'El campo apellido debe tener máximo 50 caracteres',

                'phone.required'=> 'El campo telefono es requerido',
                'phone.digits_between'=>'El campo telefono debe tener un mínimo de 7 y un máximo de 10 caracteres numericos',

                'document.required'=> 'El campo documento es requerido',
                'document.unique'=> 'El documento ingresado ya existe.',
                'document.digits_between'=>'El campo documento debe tener un mínimo de 5 y un máximo de 20 caracteres numericos',
                
                'email.required'=> 'El campo email es requerido',
                'email.email'=>'El campo email debe ser de tipo email',
                'email.unique'=> 'El email ingresado ya existe.',

                'password.required'=>'El campo contraseña es requerido',
                'password.min'=>'El campo contraseña debe tener mínimo 8 caracteres',
                'password.max'=>'El campo contraseña debe tener máximo 16 caracteres',

                'role_id.required'=>'El campo rol es requerido',
                'role_id.in'=>'El campo rol permite valores Administrador(1), Consultor(3), Vigilante(4)',
            ]
        ];
        try {
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
        
            $lastName = ($request->last_name)? $request->last_name : "";

            $user = User::create([
                'name' => $request->name,
                'last_name' => $lastName,
                'email' => $request->email,
                'phone' => $request->phone,
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
                'last_name' => 'sometimes|min:3|max:50',
                'phone' => 'required|digits_between:7,10',
                'email' => 'required|email|min:8|max:60',
                'password' => 'min:8|max:16',
                'active' => 'required|in:2,1',
                'role_id' => 'required|in:1,3,4',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 3 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 50 caracteres',
                
                'last_name.min' => 'El campo apellido debe tener mínimo 3 caracteres',
                'last_name.max' => 'El campo apellido debe tener máximo 50 caracteres',
                
                'phone.required'=> 'El campo telefono es requerido',
                'phone.digits_between'=>'El campo telefono debe tener un mínimo de 7 y un máximo de 10 caracteres numericos',

                'email.required'=> 'El campo email es requerido',
                'email.email'=>'El campo email debe ser de tipo email',
                'email.unique'=> 'El email ingresado ya existe.',

                'password.sometimes'=>'El campo contraseña es sometimes',
                'password.min'=>'El campo contraseña debe tener mínimo 8 caracteres',
                'password.max'=>'El campo contraseña debe tener máximo 16 caracteres',

                'active.required'=>'El campo estado es requerido',
                'active.in'=>'El campo estado recibe los valores Activo e Inactivo',

                'role_id.required'=>'El campo rol es requerido',
                'role_id.in'=>'El campo rol permite valores Administrador(1), Consultor(3), Vigilante(4)',

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
            if($request->last_name){
                $user->last_name = $request->input('last_name');
            }
            if($request->document){
                $user->document = $request->input('document');
            }
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
                            "No se ha conseguido actualizar la información."
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

        // $user = User::role($this->role)->where('email', $request->email)->first(); //? Permitir a cualquier usuario
        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Usuario no encontrado']]],404);
        }

        $codeRequest = VerificationCode::generate($request->email);
        $code = $codeRequest['code'];

        $currentTime = date('H:i');

        $content = "Este es el código que usará para restablecer la contraseña. Código {$code} Hora {$currentTime}";

        Biker::Notify(['phone'=>$user->phone, 'message'=>$content]);

        return response()->json(['message'=>'Success', 'response'=>['code'=>$code,'errors'=>[]]],200);

    }

    public function restorePassword(Request $request){

        // $user = User::role($this->role)->where('email', $request->email)->first(); //? Permitir a cualquier usuario
        $user = User::where('email', $request->email)->first();
        if(!$user){
            return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Usuario no encontrado']]],404);
        }

        $validation = [
            "rules" => [
                'password' => 'required|min:8|max:16',
                'code' => 'required|exists:verification_codes,code'
            ],
            "messages" => [
                'code.required'=>'El campo código de verificación es requerido',
                'code.exists'=>'El código de verificación no ha sido encontrado o ya ha sido procesado.',
                'password.required'=>'El campo contraseña es requerido',
                'password.min'=>'El campo contraseña debe tener mínimo 8 caracteres',
                'password.max'=>'El campo contraseña debe tener máximo 16 caracteres',
            ]
        ];
        try {
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            
            $vef = VerificationCode::validate($request->code, $request->email);
            
            if(!$vef){
                return response()->json(['response' => ['errors'=>['El código de verificación no acerta las credenciales con las que fue registrado.']], 'message' => 'Bad Request'], 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['message' => 'Success', 'response'=>['errors'=>[]]], 200);
        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }

    }

}
