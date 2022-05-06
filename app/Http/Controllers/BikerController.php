<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\Parameter;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Cloudder;

use App\Models\VerificationCode;

use function PHPUnit\Framework\objectEquals;

class BikerController extends Controller
{


    private $client;


    public function __construct()
    {
        if (Route::getCurrentRoute()) {
            $route = Route::getCurrentRoute()->uri();
            $this->client = (preg_match("/api\//", $route)) ? "app" : "web";
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = DB::table('bikers')
                ->join('type_documents', 'bikers.type_documents_id', '=', 'type_documents.id')
                ->join('genders', 'bikers.genders_id', '=', 'genders.id')
                ->join('jobs', 'bikers.jobs_id', '=', 'jobs.id')
                ->select(
                    'bikers.*',
                    'type_documents.name as type',
                    'genders.name as gender',
                    'jobs.name as job',
                    'bikers.neighborhoods_id as neighborhood',
                    DB::raw('SUBSTRING(bikers.levels_id, 9,9) AS levels_id')
                )
                ->whereRaw("phone != ''")
                ->get();

            $type = DB::table('type_documents')
                ->select('type_documents.name as text', 'type_documents.id as value')
                ->where('type_documents.active', '=', 1)
                ->get();
            $gender = DB::table('genders')
                ->select('genders.name as text', 'genders.id as value')
                ->where('genders.active', '=', 1)
                ->get();
            $job = DB::table('jobs')
                ->select('jobs.name as text', 'jobs.id as value')
                ->where('jobs.active', '=', 1)
                ->get();
            $level = [
                ["value" => "1", "text" => "Estrato 1"],
                ["value" => "2", "text" => "Estrato 2"],
                ["value" => "3", "text" => "Estrato 3"],
                ["value" => "4", "text" => "Estrato 4"],
                ["value" => "5", "text" => "Estrato 5"],
                ["value" => "6", "text" => "Estrato 6"],
            ];

            $parkings = DB::table('parkings')
                ->select('parkings.name as text', 'parkings.id as value')
                ->where('parkings.active', '=', 1)
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
                    'users' => $data,
                    'indexes' => [
                        'type' => $type, 'gender' => $gender, 'job' => $job, 'level' => $level, 'active' => $active, 'parkings' => $parkings
                    ]
                ]
            ],
            200
        );
    }


    public function export()
    {
        print_r('entro a export');
        return response()->json(['message' => 'exported'], 200);
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

    private function photoValidation($request, $updating = false)
    {
        $phValid = [];

        if (!$request->hasFile('photo')) {

            // While updating, resending the image won't be required
            if (!$updating) {
                $phValid[] = 'El campo fotografía es requerido';
            }
        } else {
            $ph = $request->file('photo');

            $arrayingImage = (gettype($ph) != 'array') ? [$ph] : $ph;
            $extensiones = ["jpg", "png", "jpeg"];

            if (count($arrayingImage) > 1) {
                $phValid[] = 'Se ha recibido más de una imágen para el ciclista';;
                return $phValid;
            }

            if (!$ph->isValid()) {
                $phValid[] = 'El campo fotografía es inválido';
            }

            if (!in_array($ph->getClientOriginalExtension(), $extensiones)) {
                $phValid[] = 'El campo fotografía recibe imágenes de formato jpg, jpeg y png.';
            }

            if ($ph->getSize() > 10000000) {
                $phValid[] = 'El campo fotografía tiene un tamaño máximo de 10MB';
            }
        }

        return $phValid;
    }

    public function store(Request $request)
    {

        log::info("==TRACE== biker@STORE");
        log::info($request->all());

        $validateImage = true;
        if ($request->debug) {
            return response()->json(['message' => 'Internal Error', 'response' => ['data' => ['raw' => $request->all(), 'json' => json_encode($request->all())], 'errors' => []]], 500);
        }

        $validation = [
            "rules" => [
                'name' => 'required|min:2|max:100',
                'lastName' =>  'required|min:2|max:100',
                'type' =>  'required|exists:type_documents,id',
                'document' => 'required|min:5|max:30|unique:bikers',
                'birth' =>  'required|date',
                'phone' => 'required|digits_between:7,10|unique:bikers',
                'email' => 'required|email|min:8|max:60|unique:bikers',
                'confirmation' =>   'required|exists:verification_codes,code',
                'job' =>    'required|exists:jobs,id',
                'neighborhood' =>   'required|min:2|max:160',
                'level' =>  'required|in:1,2,3,4,5,6',
                'register' =>   'required|date',
                'active' =>  'required|in:1,2,3',
                'auth' =>   'required|in:1,2',
                'gender' =>     'required|exists:genders,id',
                'parkings_id' =>     'required|exists:parkings,id',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 2 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                'lastName.required' => 'El campo apellido es requerido',
                'lastName.min' => 'El campo apellido debe tener mínimo 2 caracteres',
                'lastName.max' => 'El campo apellido debe tener máximo 100 caracteres',

                'type.required' => 'El campo tipo de documento es requerido',
                'type.exists' => 'El campo tipo de documento no acerta ningún registro existente',

                'phone.required' => 'El campo telefono es requerido',
                'phone.digits_between' => 'El campo telefono debe tener un mínimo de 7 y un máximo de 10 caracteres numericos',
                'phone.unique' => 'El telefono ingresado ya existe.',

                'document.required' => 'El campo documento es requerido',
                'document.unique' => 'El documento ingresado ya existe.',
                'document.max' => 'El campo documento debe tener máximo 30 caracteres',
                'document.min' => 'El campo documento debe tener mínimo 5 caracteres',

                'birth.required' => 'El campo fecha de nacimiento es requerido',
                'birth.date' => 'El campo fecha de nacimiento es de tipo fecha',


                'email.required' => 'El campo email es requerido',
                'email.email' => 'El campo email debe ser de tipo email',
                'email.max' => 'El campo email debe tener un máximo de 60 caracteres',
                'email.min' => 'El campo email debe tener un mínimo de 8 caracteres',
                'email.unique' => 'El email ingresado ya existe.',

                'confirmation.required' => 'El campo verificación es requerido',
                'confirmation.exists' => 'El código de verificación no ha sido encontrado o ya ha sido procesado.',

                'job.required' => 'El campo ocupacion es requerido',
                'job.exists' => 'El campo ocupacion no acerta ningún registro existente',

                'parkings_id.required' => 'El campo parqueadero es requerido',
                'parkings_id.exists' => 'El campo parqueadero no acerta ningún registro existente',

                'neighborhood.required' => 'El campo Barrio es requerido',
                'neighborhood.min' => 'El campo Barrio debe tener un mínimo de 2 caracteres',
                'neighborhood.max' => 'El campo Barrio debe tener un máximo de 160 caracteres',

                'level.required' => 'El campo Estrato es requerido',
                'level.in' => 'El campo Estrato acepta el rango 1-6',

                'register.required' => 'El campo fecha de registro es requerido',
                'register.date' => 'El campo fecha de registro es de tipo fecha',

                'active.required' => 'El campo estado del ciclista es requerido',
                'active.in' => 'El campo estado del ciclista recibe los valores Activo, Inactivo y Bloqueado',

                'auth.required' => 'El campo autorizacion es requerido',
                'auth.in' => 'El campo autorizacion recibe los valores Sí y No',

                'gender.required' => 'El campo género es requerido',
                'gender.exists' => 'El campo género no acerta ningún registro existente',
            ]
        ];

        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validateImage) {
                // Photo validation
                $phtValidation = $this->photoValidation($request);


                if ($validator->fails()) {
                    return response()->json(['message' => 'Bad Request', 'response' => ['errors' => array_merge($validator->errors()->all(), $phtValidation)]], 400);
                } else {
                    if (count($phtValidation)) {
                        return response()->json(['message' => 'Bad Request', 'response' => ['errors' => $phtValidation]], 400);
                    }
                }

                // $statusResponse = Storage::disk('local')->putFileAs('testingUpload', $request->file('photo'), 'testing.png');
                // if (!$statusResponse) {
                //     return response()->json(['message' => 'Internal Error', 'response' => ["errors" => ["Error en la manipulación de archivos."]]], 500);
                // }
            } else {
                if ($validator->fails()) {
                    return response()->json(['message' => 'Bad Request', 'response' => ['errors' => $validator->errors()->all()]], 400);
                }
            }

            $vef = VerificationCode::validate($request->confirmation, $request->phone);

            if (!$vef) {
                return response()->json(['message' => 'Bad Request', 'response' => ['errors' => ['El código de verificación no acerta las credenciales con las que fue registrado.']]], 400);
            }

            $counter = Parameter::where(['name' => 'biker_counter'])->first();
            $code = 'CP' . substr("00000" . ($counter->value + 1), -5, 5);

            $ph = $request->file('photo')->getRealPath();

            try {
                Cloudder::upload($ph, null,  array("folder" => "biker"));
                $publicId = Cloudder::getPublicId();
                $url =  Cloudder::secureShow($publicId);
                $urlImg =   str_replace('_150', '_520', $url);
            } catch (\Throwable $th) {
                return response()->json(['message' => 'Bad Request', 'response' => ['message' => 'Problema al guardar la imagen', 'error' => $th]], 500);
            }


            $biker = Biker::create([
                'name' => $request->name,
                'last_name' => $request->lastName,
                'type_documents_id' => $request->type,
                'document' => $request->document,
                'birth' => $request->birth,
                'genders_id' => $request->gender,
                'parkings_id' => $request->parkings_id,
                'phone' => $request->phone,
                'email' => $request->email,
                'code' => $code,
                'confirmation' => $request->confirmation,
                'jobs_id' => $request->job,
                'neighborhoods_id' => $request->neighborhood,
                'levels_id' => "Estrato {$request->level}",
                'register' => $request->register,
                'active' => $request->active,
                'auth' => $request->auth,
                'url_img' => $urlImg,
                'id_img' => $publicId,
            ]);

            $counter->value = $counter->value + 1;
            $counter->save();

            $biker->notifySignup($request->parkings_id);
            return response()->json(['message' => 'User Created', 'response' => ["data" => $biker, "errors" => []],], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Biker  $biker
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        try {
            $biker = ($this->client == 'web')
                ? Biker::find($id)->whereRaw("phone != ''")
                : Biker::whereRaw("document = $id and phone != ''")->first();
            if (!$biker) {
                return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['Registro de ciclista no encontrado']]], 404);
            }
            $query = $this->client == 'web' ?  'bikers.id' : 'bikers.document';
            $data = DB::table('bikers')
                ->where($query, $id,)
                ->join('type_documents', 'bikers.type_documents_id', '=', 'type_documents.id')
                ->join('genders', 'bikers.genders_id', '=', 'genders.id')
                ->join('jobs', 'bikers.jobs_id', '=', 'jobs.id')
                ->select(
                    'bikers.*',
                    'type_documents.name as type',
                    'genders.name as gender',
                    'jobs.name as job',
                    'bikers.neighborhoods_id as neighborhood',
                    DB::raw('SUBSTRING(bikers.levels_id, 9,9) AS levels_id')
                )
                ->first();

            $appUrl = config('app.url');

            //?Check if localhost
            // $appUrl = ($appUrl[strlen($appUrl) - 1] == '/') ? $appUrl : "$appUrl:8000/";

            // $bikerPhotos = Storage::allFiles("public/bikers/biker{$biker->id}");
            // $data->photo = (count($bikerPhotos)) ? $appUrl . preg_replace('/public/', 'storage', $bikerPhotos[0]) : null;

            $_bicies = DB::table('bicies')
                ->where('bikers_id', $biker->id)
                ->select('bicies.*')
                ->get();
            $bicies = array();


            foreach ($_bicies as $bicy) {

                $existingPhotos = Storage::allFiles("public/bicies/bicy{$bicy->id}");

                $frontPhoto = array_filter($existingPhotos, function ($el) {
                    return preg_match('/front_/', $el);
                });
                $bicy->image_front = (count($frontPhoto)) ? $appUrl .  preg_replace('/public/', 'storage', array_values($frontPhoto)[0])  : null;

                $backPhoto = array_filter($existingPhotos, function ($el) {
                    return preg_match('/back_/', $el);
                });
                $bicy->image_back = (count($backPhoto)) ? $appUrl .  preg_replace('/public/', 'storage', array_values($backPhoto)[0])  : null;

                $sidePhoto = array_filter($existingPhotos, function ($el) {
                    return preg_match('/side_/', $el);
                });
                $bicy->image_side = (count($sidePhoto)) ? $appUrl .  preg_replace('/public/', 'storage', array_values($sidePhoto)[0])  : null;

                $bicies[] = $bicy;
            }

            $type = DB::table('type_documents')
                ->select('type_documents.name as text', 'type_documents.id as value')
                ->where('type_documents.active', '=', 1)
                ->get();
            $gender = DB::table('genders')
                ->select('genders.name as text', 'genders.id as value')
                ->where('genders.active', '=', 1)
                ->get();
            $job = DB::table('jobs')
                ->select('jobs.name as text', 'jobs.id as value')
                ->where('jobs.active', '=', 1)
                ->get();
            $level = DB::table('levels')
                ->select('levels.name as text', 'levels.id as value')
                ->where('levels.active', '=', 1)
                ->get();

            $active = [
                ["text" => "Activo", "value" => 1],
                ["text" => "Inactivo", "value" => 2],
                ["text" => "Bloqueado", "value" => 3],
            ];

            return response()->json(
                [
                    'message' => "Sucess",
                    'response' => [
                        'data' => $data,
                        'bicies' => $bicies,
                        'indexes' => [
                            'type' => $type, 'gender' => $gender, 'job' => $job, 'level' => $level, 'active' => $active
                        ]
                    ]
                ],
                200
            );
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Biker  $biker
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(Biker::whereId($id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Biker  $biker
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = false)
    {

        try {

            Log::info('BikerUpdateRequest');
            Log::info($request->all());

            $validateImage = true;

            if (!$id) {
                $id = $request->input('id');
            }
            $data = Biker::find($id);
            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $id, 'errors' => ['Usuario No encontrado']]], 404);
            }

            $emailRules = ($data->email == $request->input('email')) ?  'email|min:8|max:60' : 'email|min:8|max:60|unique:bikers';
            $documentRules = ($data->document == $request->input('document')) ?  'min:5|max:30' : 'min:5|max:30|unique:bikers';
            $confirmationRules = ($data->phone == $request->input('phone')) ?  '' : 'exists:verification_codes,code';

            $validation = [
                "rules" => [
                    'name' => 'min:4|max:100',
                    'lastName' =>  'min:4|max:100',
                    'document' => $documentRules,
                    'type' =>  'exists:type_documents,id',
                    'document' => 'min:5|max:30',
                    'birth' =>  'date',
                    'phone' => 'digits_between:7,10',
                    'email' => $emailRules,
                    'confirmation' =>   $confirmationRules,
                    'job' =>    'exists:jobs,id',
                    'neighborhood' =>   'min:2|max:160',
                    'level' =>  'in:1,2,3,4,5,6',
                    'register' =>   'date',
                    'active' =>  'in:1,2,3',
                    'auth' =>   'in:1,2',
                    'gender' =>     'exists:genders,id',
                ],
                "messages" => [

                    'document.required' => 'El campo documento es requerido',
                    'document.unique' => 'El documento ingresado ya existe.',
                    'document.max' => 'El campo documento debe tener máximo 30 caracteres',
                    'document.min' => 'El campo documento debe tener mínimo 5 caracteres',

                    'name.required' => 'El campo nombre es requerido',
                    'name.min' => 'El campo nombre debe tener mínimo 4 caracteres',
                    'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                    'lastName.required' => 'El campo apellido es requerido',
                    'lastName.min' => 'El campo apellido debe tener mínimo 4 caracteres',
                    'lastName.max' => 'El campo apellido debe tener máximo 100 caracteres',

                    'type.required' => 'El campo tipo de documento es requerido',
                    'type.exists' => 'El campo tipo de documento no acerta ningún registro existente',

                    'phone.required' => 'El campo telefono es requerido',
                    'phone.digits_between' => 'El campo telefono debe tener un mínimo de 7 y un máximo de 10 caracteres numericos',
                    'phone.unique' => 'El telefono ingresado ya existe.',

                    'document.required' => 'El campo documento es requerido',
                    'document.unique' => 'El documento ingresado ya existe.',
                    'document.max' => 'El campo documento debe tener máximo 30 caracteres',
                    'document.min' => 'El campo documento debe tener mínimo 5 caracteres',

                    'birth.required' => 'El campo fecha de nacimiento es requerido',
                    'birth.date' => 'El campo fecha de nacimiento es de tipo fecha',

                    'email.required' => 'El campo email es requerido',
                    'email.email' => 'El campo email debe ser de tipo email',
                    'email.max' => 'El campo email debe tener un máximo de 60 caracteres',
                    'email.min' => 'El campo email debe tener un mínimo de 8 caracteres',
                    'email.unique' => 'El email ingresado ya existe.',

                    'confirmation.required' => 'El campo verificación es requerido',
                    'confirmation.exists' => 'El código de verificación no ha sido encontrado o ya ha sido procesado.',

                    'job.required' => 'El campo ocupacion es requerido',
                    'job.exists' => 'El campo ocupacion no acerta ningún registro existente',

                    'neighborhood.required' => 'El campo Barrio es requerido',
                    'neighborhood.min' => 'El campo Barrio debe tener un mínimo de 2 caracteres',
                    'neighborhood.max' => 'El campo Barrio debe tener un máximo de 160 caracteres',

                    'level.required' => 'El campo Estrato es requerido',
                    'level.in' => 'El campo Estrato acepta el rango 1-6',

                    'register.required' => 'El campo fecha de registro es requerido',
                    'register.date' => 'El campo fecha de registro es de tipo fecha',

                    'active.required' => 'El campo estado del ciclista es requerido',
                    'active.in' => 'El campo estado del ciclista recibe los valores Activo, Inactivo y Bloqueado',

                    'auth.required' => 'El campo autorizacion es requerido',
                    'auth.in' => 'El campo autorizacion recibe los valores Sí y No',

                    'gender.required' => 'El campo género es requerido',
                    'gender.exists' => 'El campo género no acerta ningún registro existente',

                ]
            ];

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);


            if ($validateImage && $request->file('photo')) {
                // photo validation
                $phtValidation = $this->photoValidation($request, true);

                if ($validator->fails()) {
                    return response()->json(['response' => ['errors' => array_merge($validator->errors()->all(), $phtValidation)], 'message' => 'Bad Request'], 400);
                } else {
                    if (count($phtValidation)) {
                        return response()->json(['response' => ['errors' => $phtValidation], 'message' => 'Bad Request'], 400);
                    }
                }
            } else {
                if ($validator->fails()) {
                    return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
                }
            }

            // Verification code, validated above, deleted here
            // if ($data->phone != $request->input('phone')) {
            //     $vef = VerificationCode::validate($request->confirmation);
            //     if (!$vef) {
            //         return response()->json(['message' => 'Bad Request', 'response' => ['errors' => ['El código de verificación no acerta las credenciales con las que fue registrado.']]], 400);
            //     }
            // }

            if ($request->file('photo')) {
                $ph     = $request->file('photo')->getRealPath();
                $id_ph = $data->id_img;
                try {
                    Cloudder::upload($ph, null,  array("folder" => "biker"));
                    $publicId = Cloudder::getPublicId();
                    $url =  Cloudder::secureShow($publicId);
                    $urlImg =   str_replace('_150', '_520', $url);

                    Cloudder::delete($id_ph);
                    Cloudder::destroyImage($id_ph);
                } catch (\Throwable $th) {
                    return response()->json(['message' => 'Bad Request', 'response' => ['msg' => 'Error al actualizar la imagen', 'error' => $th]], 500);
                }
            } else {
                $urlImg = $data->id_img;
                $publicId = $data->url_img;
            }

            $data->name = $request->name ?? $data->name;
            $data->last_name = $request->lastName ?? $data->last_name;
            $data->type_documents_id = $request->type ?? $data->type_documents_id;
            $data->document = $request->document ?? $data->document;
            $data->birth = $request->birth ?? $data->birth;
            $data->parkings_id = $request->parkings_id ?? $data->parkings_id;
            $data->genders_id = $request->gender ?? $data->genders_id;
            $data->phone = $request->phone ?? $data->phone;
            $data->email = $request->email ?? $data->email;
            $data->confirmation = $request->confirmation ?? $data->confirmation;
            $data->jobs_id = $request->job ?? $data->jobs_id;
            $data->neighborhoods_id = $request->neighborhood ?? $data->neighborhoods_id;
            $data->levels_id = $request->level ? "Estrato {$request->level}" : $data->levels_id;
            $data->register = $request->register ?? $data->register;
            $data->active = $request->active ?? $data->active;
            $data->auth = $request->auth ?? $data->auth;
            $data->url_img = $urlImg;
            $data->id_img = $publicId;
            $data->update();

            return response()->json(['message' => 'User Updated', 'response' => ["errors" => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Biker  $biker
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $data = Biker::find($id);
            if (!$data) {
                return response()->json(['message' => "Not Found", 'response' => ['errors' => ["Usuario no encontrado."]]], 404);
            }

            // Eliminando imagen de Cloudinary
            try {
                Cloudder::delete($data->id_img);
                Cloudder::destroyImage($data->id_img);
            } catch (\Throwable $th) {
                return response()->json(['message' => 'Bad Request', 'response' => ['msg' => 'Error a eliminar la imagen del ciclista', 'error' => $th]], 500);
            }



            $data->email = '';
            $data->phone = '';
            $data->url_img = '';
            $data->id_img = '';
            $data->neighborhoods_id = '';
            $data->levels_id = 'Estrato 1';
            $data->jobs_id = '1';

            $data->save();

            return response()->json(['message' => 'Success',  'response' => ['data' => ['Ciclista Anulado'], 'errors' => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }

    public function unblockBiker($id)
    {

        try {

            $biker = Biker::find($id);
            if (!$biker) {
                return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['Usuario ciclista no encontrado']]], 404);
            }
            $biker->unblockAndNotify();

            return response()->json(['message' => 'Success', 'response' => ["errors" => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    public function checkActiveness()
    {

        try {
            $currentDate = date('Y-m-d H:i:s');
            $currentYear = date('Y');
            $limitYear = intval($currentYear) - 2;
            $limitDate = preg_replace("/$currentYear/", $limitYear, $currentDate);

            $bikers = DB::table('bikers')
                ->where("bikers.updated_at", "<", $limitDate)
                ->where("bikers.active", "!=", "3")
                ->join('visits', 'bikers.id', 'visits.bikers_id')
                ->groupBy('bikers.id')
                ->select('bikers.id', DB::raw('max(visits.updated_at) as lastAction'), DB::raw("max(visits.updated_at) < '$limitDate' as inactive"))
                ->get()->toArray();

            $toBeBlockedBikers = array_values(array_filter($bikers, function ($biker) {
                if ($biker->inactive) {
                    return true;
                }
            }));
            $toBeBlockedBikers_ids = array_map(function ($biker) {
                return $biker->id;
            }, $toBeBlockedBikers);

            $smsResponses = Biker::notifyBeingBlocked($toBeBlockedBikers_ids);
            return response()->json([
                'message' => 'sucess',
                'response' => [
                    'data' => [
                        'bikers' => $toBeBlockedBikers,
                        'smsResponses' => $smsResponses
                    ],
                    'errors' => []
                ]
            ], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    public function getVerificationCode(Request $request, $phone)
    {
        return Biker::getVerificationCode($phone);
    }

    public function massiveStore(Request $request)
    {

        $validation = [
            "rules" => [
                'name' => 'required|min:2|max:100',
                'last_name' =>  'required|min:2|max:100',
                'type_documents_id' =>  'required|exists:type_documents,id',
                'document' => 'required|min:5|max:30|unique:bikers',
                'birth' =>  'required|date',
                'phone' => 'required|digits_between:7,10',
                'email' => 'required|email|min:8|max:60|unique:bikers',
                'jobs_id' =>    'required|exists:jobs,id',
                'neighborhoods_id' =>   'required|min:2|max:160',
                'levels_id' =>  'required|in:1,2,3,4,5,6',
                'register' =>   'required|date',
                'active' =>  'required|in:1,2,3',
                'auth' =>   'required|in:1,2',
                'genders_id' =>     'required|exists:genders,id',
                'parkings_id' =>     'required|exists:parkings,id',
            ],
            "messages" => [
                'name.required' => 'El campo nombre es requerido',
                'name.min' => 'El campo nombre debe tener mínimo 2 caracteres',
                'name.max' => 'El campo nombre debe tener máximo 100 caracteres',

                'last_name.required' => 'El campo apellido es requerido',
                'last_name.min' => 'El campo apellido debe tener mínimo 2 caracteres',
                'last_name.max' => 'El campo apellido debe tener máximo 100 caracteres',

                'type_documents_id.required' => 'El campo tipo de documento es requerido',
                'type_documents_id.exists' => 'El campo tipo de documento no acerta ningún registro existente',

                'phone.required' => 'El campo telefono es requerido',
                'phone.digits_between' => 'El campo telefono debe tener un mínimo de 7 y un máximo de 10 caracteres numericos',

                'document.required' => 'El campo documento es requerido',
                'document.unique' => 'El documento ingresado ya existe.',
                'document.max' => 'El campo documento debe tener máximo 30 caracteres',
                'document.min' => 'El campo documento debe tener mínimo 5 caracteres',

                'birth.required' => 'El campo fecha de nacimiento es requerido',
                'birth.date' => 'El campo fecha de nacimiento es de tipo fecha',


                'email.required' => 'El campo email es requerido',
                'email.email' => 'El campo email debe ser de tipo email',
                'email.max' => 'El campo email debe tener un máximo de 60 caracteres',
                'email.min' => 'El campo email debe tener un mínimo de 8 caracteres',
                'email.unique' => 'El email ingresado ya existe.',

                'confirmation.required' => 'El campo verificación es requerido',
                'confirmation.exists' => 'El código de verificación no ha sido encontrado o ya ha sido procesado.',

                'jobs_id.required' => 'El campo ocupacion es requerido',
                'jobs_id.exists' => 'El campo ocupacion no acerta ningún registro existente',

                'parkings_id.required' => 'El campo parqueadero es requerido',
                'parkings_id.exists' => 'El campo parqueadero no acerta ningún registro existente',

                'neighborhoods_id.required' => 'El campo Barrio es requerido',
                'neighborhoods_id.min' => 'El campo Barrio debe tener un mínimo de 2 caracteres',
                'neighborhoods_id.max' => 'El campo Barrio debe tener un máximo de 160 caracteres',

                'levels_id.required' => 'El campo Estrato es requerido',
                'levels_id.in' => 'El campo Estrato acepta el rango 1-6',

                'register.required' => 'El campo fecha de registro es requerido',
                'register.date' => 'El campo fecha de registro es de tipo fecha',

                'active.required' => 'El campo estado del ciclista es requerido',
                'active.in' => 'El campo estado del ciclista recibe los valores Activo, Inactivo y Bloqueado',

                'auth.required' => 'El campo autorizacion es requerido',
                'auth.in' => 'El campo autorizacion recibe los valores Sí y No',

                'genders_id.required' => 'El campo género es requerido',
                'genders_id.exists' => 'El campo género no acerta ningún registro existente',

            ]
        ];


        $bikers = [];
        $errors = [];
        foreach (file($request->file('csv')) as $i => $line) {
            if ($i == 0) {
                continue;
            } //? Titles Line
            $info = explode(',', $line);
            if (!count($info)) {
                $errors[] = "El contenido es inválido para la línea '$line'";
            }
            $biker = [
                'name' => $info[0],
                'last_name' => $info[1],
                'type_documents_id' => $info[2],
                'document' => $info[3],
                'phone' => $info[4],
                'email' => $info[5],
                'birth' => $info[6],
                'genders_id' => $info[7],
                'jobs_id' => $info[8],
                'neighborhoods_id' => $info[9],
                'levels_id' => $info[10],
                'auth' => $info[11],
                'parkings_id' => $info[12],
                // 'code' => $info[0],
                'active' => 1,
                'register' => date('Y-m-d'),
            ];


            $validator = Validator::make($biker, $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                $_errors = [];
                foreach ($validator->errors()->all() as $value) {
                    // $_errors[] = "$value , mientras validando la línea " . ($i +1) . " , '$line' ";
                    $_errors[] = "$value , mientras validando la línea " . ($i + 1);
                }
                $errors = array_merge($errors,  $_errors);
            }

            $bikers[] = $biker;
        }

        if (count($errors)) {
            return response()->json(['message' => 'Bad Request', 'response' => ['errors' => $errors]], 200);
        }

        $storeErrors = [];
        $Bikers = [];
        $Line = 0;
        DB::beginTransaction();
        try {
            foreach ($bikers as $i => $biker) {
                $Line = $i;
                $counter = Parameter::where(['name' => 'biker_counter'])->first();
                $code = 'CP' . substr("00000" . ($counter->value + 1), -5, 5);
                $biker['code'] = $code;
                $Biker = Biker::create($biker);
                $counter->value = $counter->value + 1;
                $counter->save();
                $Bikers[] = $Biker;
            }
            DB::commit();
        } catch (QueryException $e) {
            $code = $e->getCode();
            $str = $e->getMessage();

            log::info("== TRACING ERROR CODE == {$code} ");

            if ($code == 1062 || $code == 23000) {
                preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $str, $matches);
                $storeErrors[] = "Valor({$matches[1]}) duplicado para el campo '{$matches[2]}', en la línea $Line";
            } else {
                $storeErrors[] = $str;
            }
        }
        if (count($storeErrors)) {
            DB::rollback();
        }



        return response()->json(['message' => 'Success', 'response' => ['data' => ['bikers' => $Bikers], 'indexes' => [], 'errors' => ['storeErrors' => $storeErrors]]], 200);
    }

    public function massiveRawTextMessage(Request $request)
    {

        $validation = [
            "rules" => [
                'bikers' =>     'required',
                'message' =>     'required',
            ],
            "messages" => [
                'bikers.required' => 'El campo ciclistas es requerido',
                'message.required' => 'El campo mensaje es requerido',
            ]
        ];

        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['message' => 'Bad Request', 'response' => ['errors' => $validator->errors()->all()]], 400);
            }

            $logs = [];
            $errors = [];
            $succeses = [];
            $bikers = explode(',', $request->bikers);
            foreach ($bikers as $key => $bikerId) {

                $Biker = Biker::find($bikerId);
                if (!$Biker) {
                    $errors[] = ['id' => $bikerId, 'message' => 'No encontrado'];
                }

                $resp = $Biker->sendRawTextMessage($request->message);
                $logs[] = $resp;
                if (preg_match('/OK/', $resp)) {
                    $succeses[] = ['id' => $bikerId, 'message' => $resp];
                } else {
                    $errors[] = ['id' => $bikerId, 'message' => $resp];
                }
            }

            return response()->json(['message' => 'User Created', 'response' => ["data" => $succeses, "errors" => $errors, 'logs' => $logs],], 201);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }

        return response()->json(['message' => 'Success', 'response' => ['data' => ['massiv', 'data' => $request->all()], 'indexes' => [], 'errors' => []]], 200);
    }
}
