<?php

namespace App\Http\Controllers;

use App\Models\DetailedStickerOrder;
use Illuminate\Support\Facades\Schema;
use App\Models\Visit;
use App\Models\Bicy;
use App\Models\Parking;
use App\Models\Biker;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Cloudder;

class BicyController extends Controller
{

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
            $data = DB::table('bicies')
                ->join('parkings', 'bicies.parkings_id', '=', 'parkings.id')
                ->join('type_bicies', 'bicies.type_bicies_id', '=', 'type_bicies.id')
                ->join('bikers', 'bicies.bikers_id', '=', 'bikers.id')
                ->select('bicies.*', 'parkings.name as parking', 'type_bicies.name as type', 'bikers.document as document')
                ->get();

            $parking = DB::table('parkings')
                ->select('parkings.name as text', 'parkings.id as value')
                ->where('parkings.active', '=', 1)
                ->get();

            $typeBicies = DB::table('type_bicies')
                ->select('type_bicies.name as text', 'type_bicies.id as value')
                ->where('type_bicies.active', '=', 1)
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
                    'bicies' => $data,
                    'indexes' => [
                        'parking' => $parking, 'type' => $typeBicies, 'active' => $active
                    ]
                ]
            ],
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($parking_id, $fromInside = false, $avoidIncreasingCount = false)
    {
        $Parking = Parking::find($parking_id);
        $aumentar = 1;

        while (true) {
            $code = $Parking->code . substr("0000" . ($Parking->bike_count + $aumentar), -4, 4);

            $exists = Bicy::where(['code' => $code])->first();

            if ($exists) {
                $aumentar++;
            } else {
                break;
            }
        }

        if ($aumentar !== 1) {
            $Parking->bike_count = $Parking->bike_count + $aumentar;
            $Parking->save();
        }

        if ($fromInside) {

            // From inside does not add up
            if ($aumentar == 1 && !$avoidIncreasingCount) {
                $Parking->bike_count = $Parking->bike_count + $aumentar;
                $Parking->save();
            }

            return $code;
        } else {
            return response()->json(['message' => 'Success', 'response' => ['data' => ['consecutive' => $code], 'indexes' => [], 'errors' => []]], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    private function photoValidation($photo, $updating = false)
    {
        $phValid = [];

        if (!$photo) {

            // While updating, resending the image won't be required
            if (!$updating) {
                $phValid[] = 'El campo fotografía es requerido';
            }
        } else {

            $arrayingImage = (gettype($photo) != 'array') ? [$photo] : $photo;
            $extensiones = ["jpg", "png", "jpeg"];

            if (count($arrayingImage) > 1) {
                $phValid[] = 'Se ha recibido más de una imágen para el ciclista';;
                return $phValid;
            }

            if (!$photo->isValid()) {
                $phValid[] = 'El campo fotografía es inválido';
            }

            if (!in_array($photo->getClientOriginalExtension(), $extensiones)) {
                $phValid[] = 'El campo fotografía recibe imágenes de formato jpg, jpeg y png.';
            }

            if ($photo->getSize() > 10000000) {
                $phValid[] = 'El campo fotografía tiene un tamaño máximo de 10MB';
            }
        }

        return $phValid;
    }



    private function savePhotoInCloud($photo)
    {
        try {
            Cloudder::upload($photo, null,  array("folder" => "bicy"));
            $publicId = Cloudder::getPublicId();
            $url =  Cloudder::secureShow($publicId);
            $urlImg =   str_replace('_150', '_520', $url);

            return array($urlImg, $publicId);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Bad Request', 'response' => ['message' => 'Problema al guardar la imagen', 'error' => $th]], 500);
        }
    }


    public function store(Request $request)
    {
        $validateImage = true;

        $biker = Biker::where('document', $request->document)->first();
        if (!$biker) {
            return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['Ciclista No encontrado']]], 404);
        }


        $validation = [
            "rules" => [
                'code' => 'sometimes|min:1|max:20|unique:bicies',
                'document' => 'required',
                'parkings_id' =>   'required|exists:parkings,id',
                'brand' =>  'required',
                'color' => 'required',
                'serial' => 'required|unique:bicies',
                'tires' => 'required',
                'type_bicies_id' =>   'required|exists:type_bicies,id',
                'active' =>  'required|in:1,2,3',
            ],
            "messages" => [
                'code.required' => 'El campo codigo es requerido',
                'code.min' => 'El campo codigo debe tener mínimo 1 caracteres',
                'code.max' => 'El campo codigo debe tener máximo 20 caracteres',
                'code.unique' => 'El codigo ingresado ya existe.',
                'brand.required' => 'El campo marca es requerido',
                'color.required' => 'El campo color es requerido',
                'document.required' => 'El campo documento es requerido',
                'serial.required' => 'El campo serial o código del marco es requerido',
                'serial.unique' => 'El campo serial o código del marco ya existe en el sistema',
                'tires.required' => 'El campo llanta es requerido',
                'parkings_id.required' => 'El campo Bici Estación es requerido',
                'parkings_id.exists' => 'El campo Bici Estación no acerta ningún registro existente',
                'type_bicies_id.required' => 'El campo tipo es requerido',
                'type_bicies_id.exists' => 'El campo tipo no acerta ningún registro existente',
                'active.required' => 'El campo estado de la bicicleta es requerido',
                'active.in' => 'El campo estado de la bicicleta es recibe los valores Activo, Inactivo y Bloqueado',
            ]
        ];

        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if (true) {
                // Photo validation
                $phtValidation = $this->photoValidation($request->file('image_back'));
                $phtValidation2 = $this->photoValidation($request->file('image_side'));
                $phtValidation3 = $this->photoValidation($request->file('image_front'));
                if ($validator->fails()) {
                    return response()->json(['response' => ['errors' => array_merge($validator->errors()->all(), $phtValidation, $phtValidation2, $phtValidation3)], 'message' => 'Bad Request'], 400);
                } else {
                    if (count($phtValidation)) {
                        return response()->json(['response' => ['errors' => $phtValidation], 'message' => 'Bad Request'], 400);
                    }
                }

                // $testingImage = $request->hasFile('image_front') ? $request->file('image_front') : ( $request->hasFile('image_back') ? $request->file('image_back') : $request->file('image_side') );
                // $statusResponse = Storage::disk('local')->putFileAs('testingUpload', $testingImage, 'testing.png');
                // if (!$statusResponse) {
                //     return response()->json(['message' => 'Internal Error', 'response' => ["errors" => ["Error en la manipulación de archivos."]]], 500);
                // }

            } else {
                if ($validator->fails()) {
                    return response()->json(['response' => ['errors' => $validator->errors()->all()], 'message' => 'Bad Request'], 400);
                }
            }

            if (!$request->code) {
                $request->request->add(['code' => $this->create($request->parkings_id, true, true)]);
            }



            $image_back = $request->file('image_back')->getRealPath();
            $image_side = $request->file('image_side')->getRealPath();
            $image_front = $request->file('image_front')->getRealPath();

            list($url_image_back, $id_image_back) = $this->savePhotoInCloud($image_back);
            list($url_image_side, $id_image_side) = $this->savePhotoInCloud($image_side);
            list($url_image_front, $id_image_front) = $this->savePhotoInCloud($image_front);

            $Bicy = Bicy::create([
                'code' => $request->code,
                'description' => ($request->description) ? $request->description : '',
                'brand' => $request->brand,
                'bikers_id' => $biker->id,
                'color' => $request->color,
                'serial' => $request->serial,
                'tires' => $request->tires,
                'type_bicies_id' => $request->type_bicies_id,
                'parkings_id' => $request->parkings_id,
                'active' => $request->active,
                'url_image_back' => $url_image_back,
                'url_image_side' => $url_image_side,
                'url_image_front' => $url_image_front,
                'id_image_back' => $id_image_back,
                'id_image_side' => $id_image_side,
                'id_image_front' => $id_image_front
            ]);

            $Parking = Parking::find($request->parkings_id);
            $Parking->bike_count = $Parking->bike_count + 1;
            $Parking->save();

            //? Usefull while deving, maybe not so much on production, can't harm tho'
            // $existingPhotos = Storage::allFiles("public/bicies/bicy{$Bicy->id}");
            // Storage::delete($existingPhotos);

            // if($validateImage){

            //     if($request->hasFile('image_back')){
            //         $hash = sha1(date('H:i:s'));
            //         $statusResponse = Storage::disk('local')->putFileAs("public/bicies/bicy{$Bicy['id']}", $request->file('image_back') , "back_$hash.png");
            //     }
            //     if($request->hasFile('image_side')){
            //         $hash = sha1(date('H:i:s'));
            //         $statusResponse = Storage::disk('local')->putFileAs("public/bicies/bicy{$Bicy['id']}", $request->file('image_side') , "side_$hash.png");
            //     }
            //     if($request->hasFile('image_front')){
            //         $hash = sha1(date('H:i:s'));
            //         $statusResponse = Storage::disk('local')->putFileAs("public/bicies/bicy{$Bicy['id']}", $request->file('image_front') , "front_$hash.png");
            //     }

            // }

            if ($Bicy) {
                $smsResponse = $biker->notifyBicySignin($Bicy->id);
            }

            $stickerOrder = DetailedStickerOrder::create([
                'bicies_id' => $Bicy->id,
                'parkings_id' => $request->parkings_id,
                'users_id' => $request->user()->id,
                'active' => '1',
            ]);

            return response()->json(['message' => 'Bicy Created', 'response' => ["data" => $Bicy]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bicy  $Bicy
     * @return \Illuminate\Http\Response
     */
    public function show($id, $detailed = false)
    {
        if ($this->client == 'app') {
            $data = Bicy::where(['bicies.code' => $id])
                ->join('bikers', 'bikers.id', 'bicies.bikers_id')
                ->select('bicies.*', 'bikers.document')
                ->first();
        } else {
            $data = Bicy::where(['bicies.id' => $id])
                ->join('bikers', 'bikers.id', 'bicies.bikers_id')
                ->select('bicies.*', 'bikers.document')
                ->first();
        }

        if (!$data) {
            return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['Registro de bicicleta no encontrado']]], 404);
        }

        $id = $data->id;
        $existVisit = Visit::where(['bicies_id'=>$id, 'duration'=>0])->first();

        $isDisabled = $existVisit ? true : false;


        if ($detailed) {
            $biker = $data->biker;
            return response()->json(['message' => "Sucess", 'response' => ['bicy' => $data, 'biker' => $biker , 'visit'=> $isDisabled ]], 200);
        } else {
            return response()->json(['message' => "Sucess", 'response' => ['data' => $data,]], 200);
        }
    }

    public function detailedShow($id)
    {
        return $this->show($id, true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bicy  $Bicy
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = DB::table('bicies')
            ->join('bikers', 'bicies.bikers_id', '=', 'bikers.id')
            ->select('bicies.*', 'bikers.document as document')
            ->where('bicies.id', '=', $id)
            ->first();

        if (!$data) {
            return response()->json(['message' => "Not Found", 'response' => ['errors' => ["Bicicleta no encontrada."]]], 404);
        }

        return response()->json(['message' => 'Success', 'response' => [
            'bicies' => $data,
            'errors' => []
        ]], 200);
    }

    private function updatePhotoInCloud($photo, $id_photo)
    {
        try {
            Cloudder::upload($photo, null,  array("folder" => "bicy"));
            $publicId = Cloudder::getPublicId();
            $url =  Cloudder::secureShow($publicId);
            $urlImg =   str_replace('_150', '_520', $url);

            Cloudder::delete($id_photo);
            Cloudder::destroyImage($id_photo);
            return array($urlImg, $publicId);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Bad Request', 'response' => ['message' => 'Problema al guardar la imagen', 'error' => $th]], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bicy  $Bicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = false)
    {

        $validateImage = true;

        try {

            if (!$id) {
                $id = $request->input('id');
            }
            $data = Bicy::find($id);
            if (!$data) {
                return response()->json(['message' => 'Not Found', 'response' => ['id' => $id, 'errors' => ['Bicicleta No encontrada']]], 404);
            }

            $biker = Biker::where('document', $request->document)->first();
            if (!$biker) {
                return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['Ciclista No encontrado']]], 400);
            }


            $codeRules = ($request->code != $data->code) ? 'min:1|max:20|unique:bicies' : 'min:1|max:20';
            $serialRules = ($request->serial != $data->serial) ? '|unique:bicies' : '';

            $validation = [
                "rules" => [
                    'code' => $codeRules,
                    'type_bicies_id' =>   'exists:type_bicies,id',
                    'serial' => $serialRules,
                    'parkings_id' =>   'exists:parkings,id',
                    'active' =>  'in:1,2,3',
                ],
                "messages" => [
                    'code.min' => 'El campo codigo debe tener mínimo 1 caracteres',
                    'code.max' => 'El campo codigo debe tener máximo 20 caracteres',
                    'code.unique' => 'El codigo ingresado ya existe.',
                    'description.min' => 'El campo caracteristicas debe tener mínimo 5 caracteres',
                    'description.max' => 'El campo caracteristicas debe tener máximo 200 caracteres',
                    'parkings_id.exists' => 'El campo Bici Estación no acerta ningún registro existente',
                    'type_bicies_id.exists' => 'El campo tipo no acerta ningún registro existente',
                    'serial.unique' => 'El campo serial o código del marco ya existe',
                    'active.in' => 'El campo estado de la bicicleta es recibe los valores Activo, Inactivo y Bloqueado',
                ]
            ];

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validateImage) { // photo validation

                $phtValidation = $this->photoValidation($request->file('image_back'), true);
                $phtValidation2 = $this->photoValidation($request->file('image_side'), true);
                $phtValidation3 = $this->photoValidation($request->file('image_front'), true);

                if ($validator->fails()) {
                    return response()->json(['response' => ['errors' => array_merge($validator->errors()->all(), $phtValidation, $phtValidation2 , $phtValidation3)], 'message' => 'Bad Request'], 400);
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


            if (!$request->file('image_back')) {
                $url_image_back =  $data->url_image_back;
                $id_image_back = $data->id_image_back;
            } else {
                $image_back = $request->file('image_back')->getRealPath();
                list($url_image_back, $id_image_back) = $this->updatePhotoInCloud($image_back, $data->id_image_back);
            }

            if (!$request->file('image_side')) {
                $url_image_side =  $data->url_image_side;
                $id_image_side = $data->id_image_side;
            } else {
                $image_side = $request->file('image_side')->getRealPath();
                list($url_image_side, $id_image_side) = $this->updatePhotoInCloud($image_side, $data->id_image_side);
            }

            if (!$request->file('image_front')) {
                $url_image_front =  $data->url_image_front;
                $id_image_front = $data->id_image_front;
            } else {
                $image_front = $request->file('image_front')->getRealPath();
                list($url_image_front, $id_image_front) = $this->updatePhotoInCloud($image_front, $data->id_image_front);
            }

            $data->code = $request->code ?? $data->code;
            $data->description = $request->description ?? $data->description;
            $data->brand = $request->brand ?? $data->brand;
            $data->serial = $request->serial ?? $data->serial;
            $data->bikers_id = $biker->id ?? $data->bikers_id;
            $data->color = $request->color ?? $data->color;
            $data->parkings_id = $request->parkings_id ?? $data->parkings_id;
            $data->tires = $request->tires ?? $data->tires;
            $data->type_bicies_id = $request->type_bicies_id ?? $data->type_bicies_id;
            $data->active = $request->active ?? $data->active;
            $data->url_image_back = $url_image_back;
            $data->url_image_front = $url_image_front;
            $data->url_image_side = $url_image_side;
            $data->id_image_back = $id_image_back;
            $data->id_image_front = $id_image_front;
            $data->id_image_side = $id_image_side;
            $data->update();

            $output = [];

            $smsResponse = $biker->notifyBicyUpdate($data->id);
            return response()->json(['message' => 'Bicy Updated', 'response' => ["data" => $data, "errors" => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bicy  $Bicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // return response()->json(['message' => 'Bad Request', 'response' => ['errors' => ['La eliminación de registros de bicicleta no es una funcionalidad del sistema.']]], 400);
        try {
            $data = Bicy::find($id);
            if (!$data) {
                return response()->json(['message' => "Not Found", 'response' => ['errors' => ["Bicicleta no encontrado."]]], 404);
            }


            //? Solution A, deactivate foreign key checks in order for it not to throw because of inventory or visits records
            Schema::disableForeignKeyConstraints();

            $stickers = DetailedStickerOrder::where(['bicies_id' => $data->id])->get();
            foreach ($stickers as $sticker) { //? Stickers can be deleted
                $sticker->delete();
            }

            // Eliminando imagen de Cloudinary
            try {
                Cloudder::delete($data->id_image_back);
                Cloudder::destroyImage($data->id_image_back);

                Cloudder::delete($data->id_image_side);
                Cloudder::destroyImage($data->id_image_side);

                Cloudder::delete($data->id_image_front);
                Cloudder::destroyImage($data->id_image_front);

            } catch (\Throwable $th) {
                return response()->json(['message' => 'Bad Request', 'response' => ['msg' => 'Error a eliminar la imagen del ciclista', 'error' => $th]], 500);
            }
            $data->delete();

            Schema::enableForeignKeyConstraints();
            return response()->json(['message' => 'Bicy Deleted',  'response' => ['errors' => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            Schema::enableForeignKeyConstraints();
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }

    public function checkBiciesExpirations()
    {

        $currentDate = date('Y-m-d H:i:s');
        $limitDate = date('Y-m-d H:i:s', strtotime($currentDate . " - 30 days"));
        $bicies = Bicy::where("bicies.updated_at", "<", $limitDate)
            ->where('bicies.active', 1)
            ->join('visits', 'bicies.id', 'visits.bicies_id')
            ->where('visits.duration', '0') // If it's still inside
            ->groupBy('bicies.id', 'bicies.bikers_id')
            ->select('bicies.id', 'bicies.bikers_id', DB::raw('max(visits.updated_at) as lastAction'), DB::raw("max(visits.updated_at) < '$limitDate' as inactive"), 'visits.parkings_id')
            ->get();

        //Actual processed bicies
        $_bicies = array();
        $smsResponses = [];
        foreach ($bicies as $bici) {
            $bici->biker;

            // If it hasn't been already notified 
            if (!$bici->abandonNotification()->where('active', '1')->first()) {
                $smsResponses[] = $bici->biker->notifyBikeExpiration($bici->id, $bici->parkings_id);
                $_bicies[] = $bici;
            }
        }

        return response()->json(['message' => 'sucess', 'response' => ['data' => ['LIMIT DATE' => $limitDate, 'BICIES' => $_bicies, 'SMSRESPONSES' => $smsResponses],  'errors' => []]], 200);
    }

    public function  checkAbandonedBicies()
    {
        $currentDate = date('Y-m-d H:i:s');
        $limitDate = date('Y-m-d H:i:s', strtotime($currentDate . " - 37 days"));
        $notificationLimitDate = date('Y-m-d H:i:s', strtotime($currentDate . " - 7 days"));
        $bicies = Bicy::where("bicies.updated_at", "<", $limitDate)
            ->where('bicies.active', 1)
            ->join('visits', 'bicies.id', 'visits.bicies_id')
            ->where('visits.duration', '0') // If it's still inside
            ->groupBy('bicies.id', 'bicies.bikers_id')
            ->select('bicies.id', 'bicies.bikers_id', DB::raw('max(visits.updated_at) as lastAction'), DB::raw("max(visits.updated_at) < '$limitDate' as inactive"), 'visits.parkings_id')
            ->get();

        //Actual processed bicies
        $_bicies = array();
        $smsResponses = [];
        foreach ($bicies as $bici) {
            $bici->biker;
            // If it has been already notified 
            if ($bici->abandonNotification()->where('active', '1')->where('created_at', '<', $notificationLimitDate)->first()) {
                $_bicies[] = $bici;
                $smsResponses[] = $bici->biker->notifyBikeAbandoning($bici->id, $bici->parkings_id);
            }
        }

        return response()->json(['message' => 'sucess', 'response' => ['data' => ['LIMIT DATE' => $limitDate, 'BICIES' => $_bicies, 'SMSRESPONSES' => $smsResponses],  'errors' => []]], 200);
    }

    public function returnQRInfo(Request $request)
    {
        $ids = explode(',', $request->code);

        $bicies = DB::table('bicies')
            ->join('bikers', 'bicies.bikers_id', '=', 'bikers.id')
            ->join('type_documents', 'bikers.type_documents_id', '=', 'type_documents.id')

            ->select('bicies.id', 'bicies.code', 'type_documents.code as document_type', 'bikers.document as document')

            ->whereIn('bicies.id',  $ids)
            ->get();

        if (!$bicies->count()) {
            return response()->json(['message' => 'Not Found', 'response' => ['errors' => ['No se ha encontrado ninguna bicicleta bajo la información provista.']]], 404);
        }

        $data = array();
        foreach ($bicies as $bicy) {
            $data[$bicy->id] = $bicy;
        }


        return response()->json(['message' => 'Success', 'response' => ['data' => $data, 'errors' => []]], 200);
    }

    public function massiveStore(Request $request)
    {

        $validation = [
            "rules" => [
                // 'code' => 'required|min:1|max:20|unique:bicies',
                'document' => 'required|exists:bikers,document',
                'parkings_id' =>   'required|exists:parkings,id',
                'brand' =>  'required',
                'color' => 'required',
                'tires' => 'required',
                'type_bicies_id' =>   'required|exists:type_bicies,id',
                'active' =>  'required|in:1,2,3',
            ],
            "messages" => [
                'code.required' => 'El campo codigo es requerido',
                'code.min' => 'El campo codigo debe tener mínimo 1 caracteres',
                'code.max' => 'El campo codigo debe tener máximo 20 caracteres',
                'code.unique' => 'El codigo ingresado ya existe.',
                'brand.required' => 'El campo marca es requerido',
                'color.required' => 'El campo color es requerido',
                'document.required' => 'El campo documento del ciclista es requerido',
                'document.exists' => 'El campo documento del ciclista no acerta ningún registro existente',
                'tires.required' => 'El campo llanta es requerido',
                'parkings_id.required' => 'El campo Bici Estación es requerido',
                'parkings_id.exists' => 'El campo Bici Estación no acerta ningún registro existente',
                'type_bicies_id.required' => 'El campo tipo es requerido',
                'type_bicies_id.exists' => 'El campo tipo no acerta ningún registro existente',
                'active.required' => 'El campo estado de la bicicleta es requerido',
                'active.in' => 'El campo estado de la bicicleta es recibe los valores Activo, Inactivo y Bloqueado',
            ]
        ];

        $bicies = [];
        $errors = [];
        foreach (file($request->file('csv')) as $i => $line) {
            if ($i == 0) {
                continue;
            } //? Titles Line
            $info = explode(',', $line);
            if (!count($info)) {
                $errors[] = "El contenido es inválido para la línea '$line'";
            }
            $bicy = [
                'brand' => $info[0],
                'color' => $info[1],
                'document' => $info[2],
                'tires' => $info[3],
                'parkings_id' => $info[4],
                'type_bicies_id' => $info[5],
                'active' => 1,
            ];


            $validator = Validator::make($bicy, $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                $_errors = [];
                foreach ($validator->errors()->all() as $value) {
                    // $_errors[] = "$value , mientras validando la línea " . ($i +1) . " , '$line' ";
                    $_errors[] = "$value , mientras validando la línea " . ($i + 1);
                }
                $errors = array_merge($errors,  $_errors);
            }

            $bicies[] = $bicy;
        }

        if (count($errors)) {
            return response()->json(['message' => 'Bad Request', 'response' => ['errors' => $errors]], 200);
        }

        $storeErrors = [];
        $Bicies = [];
        $Bikers = [];
        $Line = 0;
        DB::beginTransaction();
        try {

            foreach ($bicies as $i => $bicy) {
                $Line = $i;

                $code = $this->create($bicy['parkings_id'], true);
                if (!$code) {
                    $storeErrors[] = 'No se ha conseguido asignar el código consecutivo a la bicicleta';
                    continue;
                }

                $bicy['code'] = $code;

                if (!array_key_exists($bicy['document'], $Bikers)) {
                    $Biker[$bicy['document']] = Biker::where(['document' => $bicy['document']])->first();
                }

                $bicy['bikers_id'] = $Biker[$bicy['document']]->id;
                $Bicy = Bicy::create($bicy);
                $Bicies[] = $Bicy;
            }
        } catch (QueryException $e) {

            $code = $e->getCode();
            $str = $e->getMessage();
            if ($code == 1062 || $code == 23000) {
                preg_match("/Duplicate entry '(.*?)' for key '(.*?)'/", $str, $matches);
                $storeErrors[] = "Valor({$matches[1]}) duplicado para el campo '{$matches[2]}', en la línea $Line";
            } else {
                $storeErrors[] = $str;
            }
        }

        if (count($storeErrors)) {
            DB::rollback();
        } else {
            DB::commit();
        }



        return response()->json(['message' => 'Success', 'response' => ['data' => ['bicies' => $Bicies], 'indexes' => [], 'errors' => ['storeErrors' => $storeErrors]]], 200);
    }
}
