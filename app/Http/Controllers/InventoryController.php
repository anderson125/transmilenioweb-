<?php

namespace App\Http\Controllers;

use App\Models\Bicy;
use App\Models\Inventory;
use App\Models\InventoryBicy;
use App\Models\Visit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = DB::table('inventories')
                ->join('parkings', 'parkings.id', '=', 'inventories.parkings_id')
                ->select('inventories.*', 'parkings.name as parking','parkings.id as parking_id')
                ->get();

            $parking = DB::table('parkings')
                ->select('parkings.name as text', 'parkings.id as value')
                ->where('parkings.active', '=', 1)
                ->get();
            $status = DB::table('visit_statuses')
                ->select('visit_statuses.name as text', 'visit_statuses.id as value')
                ->where('visit_statuses.active', '=', 1)
                ->get();
          
            $active = [
                ["text" => "Activo", "value" => 1],
                ["text" => "Inactivo", "value" => 2],
                ["text" => "Bloqueado", "value" => 3],
            ];
            
            return response()->json(['message'=>'Success', 'response'=>[
                    'data'=> $data,
                    'indexes'=>[
                        'parkings'=>$parking
                    ],                    
                    'errors'=>[]]],200);

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
     * By petition an endpoint which will do several tasks at once
     * bcz it was too hard to do three petitions (+1 @show)
     */    

    public function storeInventoryStoreBiciesUpdateInventoryShowInventory(Request $request){
        $validation = [
            "rules" => [
                'parkings_id' => 'required|exists:parkings,id',
                // 'date' => 'required|date_format:Y-m-d',
                'bicies_code' => 'required',
            ],
            "messages" => [
                'parkings_id.required' => 'El campo parqueadero es requerido',
                'parkings_id.exists' => 'El campo parqueadero no acerta ningún registro existente',

                // 'date.required' => 'El campo fecha inicio del inventario es requerido',
                // 'date.date_format' => 'El campo fecha inicio del inventario es de tipo fecha (Y-m-d)',

                'bicies_code.required' => 'El campo código(s) de bicicleta(s) es requerido',
            ]
        ];
        $biciesIndexedById = [];
        try{
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            $hoy = date("Y-m-d");

            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            

            $checkIfASameDateInventoryExists = Inventory::where(['date'=>$hoy, 'parkings_id'=>$request->parkings_id])
                ->join('parkings', 'parkings.id', '=', 'inventories.parkings_id')
                ->select('inventories.*', 'parkings.name as parking','parkings.id as parking_id')
            ->first();
            // if($checkIfASameDateInventoryExists){
            //     return response()->json(['message'=>'Error!', 'response'=>['errors'=>
            //     ['El parqueadero seleccionado ya tiene un inventario para la fecha ingresada, 
            //     para agregar registros de bicicletas utilizar el punto de acceso respectivo']]],202);
            // }

            DB::beginTransaction();
            $inventory = Inventory::create([
                'parkings_id' => $request->parkings_id,
                'users_id' => $request->user()->id,
                'date' => $hoy,
                'active' => '1',
            ]);

            $bicies = explode(',',$request->bicies_code);
            $success = [];
             $error = [];
            foreach($bicies as $bicy){
                
                //Check if repeated in input
                if(array_key_exists($bicy,$error)  || array_key_exists($bicy,$success)){ continue; }

                $Bicy = Bicy::where('code',$bicy)->first();
                if(!$Bicy){
                    $error[$bicy] = 'Registro de bicicleta no encontrado'; 
                    continue;
                }
                $biciesIndexedById[$Bicy->id] = $Bicy;

                $inventoryBicy = InventoryBicy::create([
                    'inventory_id' => $inventory->id,
                    'bicies_id' => $Bicy->id,
                ]);

                if($inventoryBicy->id){
                    $success[$bicy] = $inventoryBicy;
                }else{
                    $error[$bicy] = 'Error inesperado al guardar.';                    
                }
            }

            # Ciclas que registró el vigilante 
	        $totalRegistered = count($biciesIndexedById);

            # Ciclas que registró el vigilante pero no tienen visita activa en la app
            $nonActiveButRegistered = [];
            foreach($biciesIndexedById as $bike){
                $visit = Visit::where([
                    'parkings_id' => $inventory->parkings_id,
                    'bicies_id'=>$bike->id,
                    'duration'=>0
                ])->get();

                if(!$visit->count()){
                    $nonActiveButRegistered[] = $bike->id;
                }
            }

            # Las ciclas que tienen una visita activa en la app pero el vigilante no registró
            $activeButNotRegistered = [];
            $visits = $visit = Visit::where([
                    'parkings_id' => $inventory->parkings_id,
                    'duration'=>0
                ])->get();
            foreach($visits as $visit){
                $currentBicy = $visit->bicies_id;
                $hasActiveVisitAndWasntRegistered = true;
                foreach($biciesIndexedById as $bike){
                    if($bike->id == $visit->bicies_id){
                        $hasActiveVisitAndWasntRegistered = false;
                        break;
                    }
                }

                if($hasActiveVisitAndWasntRegistered){
                    $activeButNotRegistered[] = $currentBicy ;   
                }
            }

            $inventory->active = '0';
            $inventory->totalRegistered = $totalRegistered;
            $inventory->nonActiveButRegistered = json_encode($nonActiveButRegistered);
            $inventory->activeButNotRegistered = json_encode($activeButNotRegistered);
            $inventory->save();
            // Report
            $nonActiveButRegistered = [];
            $_nonActiveButRegistered = json_decode($inventory->nonActiveButRegistered,true);
            foreach($_nonActiveButRegistered as $bike_id){
                $nonActiveButRegistered[] = "empty string  representing bicy id = {$bike_id}";
            }

            $activeButNotRegistered = [];
            $_activeButNotRegistered = json_decode($inventory->activeButNotRegistered,true);
            foreach($_activeButNotRegistered as $bike_id){
                $activeButNotRegistered[] = "empty string  representing bicy id = {$bike_id}";
            }

            $report = [
                'totalRegistered'=>$inventory->totalRegistered,
                'nonActiveButRegistered'=>count($nonActiveButRegistered),
                'activeButNotRegistered'=>count($activeButNotRegistered),
            ];
            DB::commit();

            return response()->json(['message'=>'Success', 'response'=>['data'=>['inventory'=>$inventory, 'report'=>$report], 'indexes'=>[], 'errors'=>[]]],200);
        }catch (QueryException $ex) {
            DB::rollBack();
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage(), 'biciesIndexedById'=>$biciesIndexedById]], 500);
        }catch(Exception $ex){
            DB::rollBack();            
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }
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
                'parkings_id' => 'required|exists:parkings,id',
                'date' => 'required|date_format:Y-m-d',
            ],
            "messages" => [
                'parkings_id.required' => 'El campo parqueadero es requerido',
                'parkings_id.exists' => 'El campo parqueadero no acerta ningún registro existente',

                'date.required' => 'El campo fecha del inventario es requerido',
                'date.date_format' => 'El campo fecha del inventario es de tipo fecha (Y-m-d)',

            ]
        ];
        try {
            $currentDay = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime("$currentDay + 1 days"));
            $currentDate = date('Y-m-d H:i');

            $x = ["$currentDay 22:00","$tomorrow 04:00"];
            $start_ts = strtotime($x[0]);
            $end_ts = strtotime($x[1]);
            $user_ts = strtotime($currentDate);

            if(!(($user_ts >= $start_ts) && ($user_ts <= $end_ts))){
                // return response()->json(['message' => 'Requested Range Not Satisfiable', 'response' => ['errors'=>['El módulo de inventario no está activo para el día actual.']]], 416);
            }

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            $inventory = Inventory::create([
                'parkings_id' => $request->parkings_id,
                'users_id' => $request->user()->id,
                'date' => $request->date,
                'active' => '1',
            ]);
            return response()->json(['message'=>'Success', 'response'=>['data'=>['id'=>$inventory->id], 'errors'=>[]]],201);
        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function showByDateAndParking(Request $request)
    {
        try {
            $inventory = Inventory::where(['date'=>$request->date, 'parkings_id'=>$request->parkings_id])
                ->join('parkings', 'parkings.id', '=', 'inventories.parkings_id')
                ->select('inventories.*', 'parkings.name as parking','parkings.id as parking_id')
                ->first();

            if(!$inventory){
                return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Registro no encontrado'],'data'=>['request'=>$request->all()] ]],404);
            }

            $typeBicies = DB::table('type_bicies')
                ->select('type_bicies.name as text', 'type_bicies.id as value')
                ->where('type_bicies.active', '=', 1)
            ->get();


            $bicies = $inventory->bicies;
            
            $report = null;
            
            if($inventory->active == 0){
                $nonActiveButRegistered = [];
                $_nonActiveButRegistered = json_decode($inventory->nonActiveButRegistered,true);
                foreach($_nonActiveButRegistered as $bike_id){
                    $nonActiveButRegistered[] = Bicy::find($bike_id);
                }

                $activeButNotRegistered = [];
                $_activeButNotRegistered = json_decode($inventory->activeButNotRegistered,true);
                foreach($_activeButNotRegistered as $bike_id){
                    $activeButNotRegistered[] = Bicy::find($bike_id);
                }

                $report = [
                    'totalRegistered'=>$inventory->totalRegistered,
                    'nonActiveButRegistered'=>$nonActiveButRegistered,
                    'activeButNotRegistered'=>$activeButNotRegistered,
                ];
            }

            return response()->json(['message'=>'Success', 'response'=>[
                'data'=> $inventory,   
                'report'=>$report,
                'indexes' => [ 'type' => $typeBicies ],     
                'errors'=>[]]
            ],200);

        }catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }

    }


    public function show($id)
    {
        try {
            $inventory = Inventory::where('inventories.id',$id)
                ->join('parkings', 'parkings.id', '=', 'inventories.parkings_id')
                ->select('inventories.*', 'parkings.name as parking','parkings.id as parking_id')
                ->first();

            $typeBicies = DB::table('type_bicies')
                ->select('type_bicies.name as text', 'type_bicies.id as value')
                ->where('type_bicies.active', '=', 1)
                ->get();

            $bicies = $inventory->bicies;
            
            $report = null;
            
            if($inventory->active == 0){
                $nonActiveButRegistered = [];
                $_nonActiveButRegistered = json_decode($inventory->nonActiveButRegistered,true);
                foreach($_nonActiveButRegistered as $bike_id){
                    $nonActiveButRegistered[] = Bicy::find($bike_id);
                }

                $activeButNotRegistered = [];
                $_activeButNotRegistered = json_decode($inventory->activeButNotRegistered,true);
                foreach($_activeButNotRegistered as $bike_id){
                    $activeButNotRegistered[] = Bicy::find($bike_id);
                }

                $report = [
                    'totalRegistered'=>$inventory->totalRegistered,
                    'nonActiveButRegistered'=>$nonActiveButRegistered,
                    'activeButNotRegistered'=>$activeButNotRegistered,
                ];
            }

            return response()->json(['message'=>'Success', 'response'=>[
                'data'=> $inventory,   
                'report'=>$report,
                'indexes' => [ 'type' => $typeBicies ],     
                'errors'=>[]]
            ],200);

        }catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message'=>'Internal Error', 'response'=>['errors'=>[$th->getMessage()]]],500);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = DB::table('inventories')
            ->select('inventories.id','inventories.active', 'inventories.parkings_id','inventories.date')
            ->where('inventories.id', '=', $id)
            ->first();

        if (!$data) {
            return response()->json(['message' => "Not Found", 'response' => ['errors' => ["Inventario no encontrado."]]], 404);
        }

        return response()->json(['message' => 'Success', 'response' => [
            'inventories' => $data,
            'errors' => []
        ]], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
  
  public function update(Request $request,$id)
    {

        $request->request->add(['inventories_id'=>$id]);
        $validation = [
            "rules" => [
                'inventories_id' => 'required|exists:inventories,id',
            ],
            "messages" => [
                'inventories_id.required' => 'El campo inventario es requerido',
                'inventories_id.exists' => 'El campo inventario no acerta ningún registro existente',
            ]
        ];
        try {
        
            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);
            if ($validator->fails()) {
                return response()->json(['response' => ['errors'=>$validator->errors()->all()], 'message' => 'Bad Request'], 400);
            }
            
            $inventory = Inventory::find($id);
            
            if($inventory->active == 0){
                return response()->json(['response' => ['errors'=>['El inventario ya se encuentra cerrado.']], 'message' => 'Bad Request'], 400);                
            }


            # Ciclas que registró el vigilante 
	        $totalRegistered = $inventory->bicies->count();

            # Ciclas que registró el vigilante pero no tienen visita activa en la app
            $nonActiveButRegistered = [];
            foreach($inventory->bicies as $bike){
                $visit = Visit::where([
                    'parkings_id' => $inventory->parkings_id,
                    'bicies_id'=>$bike->id,
                    'duration'=>0
                ])->get();

                if(!$visit->count()){
                    $nonActiveButRegistered[] = $bike->id;
                }
            }

            # Las ciclas que tienen una visita activa en la app pero el vigilante no registró
            $activeButNotRegistered = [];
            $visits = $visit = Visit::where([
                    'parkings_id' => $inventory->parkings_id,
                    'duration'=>0
                ])->get();
            foreach($visits as $visit){
                $currentBicy = $visit->bicies_id;
                $bool = true;
                foreach($inventory->bicies as $bike){
                    if($bike->id == $visit->bicies_id){
                        $bool = false;
                        break;
                    }
                }

                if($bool){
                    $activeButNotRegistered[] = $currentBicy ;   
                }
            }

            $inventory->active = '0';
            $inventory->totalRegistered = $totalRegistered;
            $inventory->nonActiveButRegistered = json_encode($nonActiveButRegistered);
            $inventory->activeButNotRegistered = json_encode($activeButNotRegistered);
            $inventory->save();

            return response()->json(['message'=>'Success', 'response'=>['errors'=>[]]],200);
        } catch (QueryException $ex) {
            return response()->json(['message' => 'Internal Error', 'response' => ['errors'=>$ex->getMessage()]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $request->request->add(['id' => $id]);

            $validation = [
                "rules" => [
                    'id' => 'required|exists:inventories,id',
                ],
                "messages" => [
                    'id.required' => 'El campo inventario es requerido',
                    'id.exists' => 'El campo inventario no acerta ningún registro existente'
                ]
            ];

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['message' => 'Bad Request',  'response' => ['errors' => $validator->errors()->all()]], 400);
            }

            $data = Inventory::find($id);
            $data->delete();
            return response()->json(['message' => 'User Deleted',  'response' => ['errors' => []]], 200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ['errors' => [$th->getMessage()]]], 500);
        }
    }
}
