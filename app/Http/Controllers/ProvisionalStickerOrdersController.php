<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProvisionalStickerOrder;
use App\Models\Parking;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProvisionalStickerOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ProvisionalStickerOrder::orderBy('created_at','desc')->get();
        $parkings = Parking::all();
        return response()->json(['message'=>'Success', 'response'=>[ 'data'=>$data, 'indexes'=>['parkings'=>$parkings],'errors'=>[]]],200);
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
                'parkings_id' =>   'required|exists:parkings,id',
                'quantity' =>  'required|integer',
            ],
            "messages" => [

                'quantity.required' => 'El campo cantidad es requerido',
                'quantity.integer' => 'El campo cantidad es de tipo numérico',

                'parkings_id.required' => 'El campo Bici Estación es requerido',
                'parkings_id.exists' => 'El campo Bici Estación no acerta ningún registro existente',
            ]
        ];

        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all() ], 'message' => 'Bad Request'], 400);
            } 


            $previous = ProvisionalStickerOrder::where(['parkings_id'=>$request->parkings_id])
                ->orderBy('created_at','desc')
                ->first();
            $prevQuantity = ($previous) ? $previous->final_consecutive + 1 : 1;

            $order = ProvisionalStickerOrder::create([
                'parkings_id'=>$request->parkings_id,
                'quantity'=> $request->quantity ,
                'printed'=> 0,
                'initial_consecutive'=> $prevQuantity,
                'final_consecutive'=> ($prevQuantity + $request->quantity  - 1)
            ]);

            return response()->json(['message' => 'Success', 'response' => ['data'=>$order, "errors" => [$previous]],], 201);

        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $data = ProvisionalStickerOrder::where(['provisional_sticker_orders.id'=>$id])
            ->join('parkings','provisional_sticker_orders.parkings_id','parkings.id')
            ->select('provisional_sticker_orders.*', 'parkings.name as parking', 'parkings.code as parking_code')
        ->first();
        return response()->json(['message'=>'Success', 'response'=>[ 'data'=>$data, 'errors'=>[]]],200);

        $data = ProvisionalStickerOrder::where(['parkings_id'=>$id])->get();
        $parkings = Parking::where(['id'=>$id])->get();
        return response()->json(['message'=>'Success', 'response'=>[ 'data'=>$data, 'indexes'=>['parkings'=>$parkings],'errors'=>[]]],200);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $request->request->add(['order_id'=>$id]);

         $validation = [
            "rules" => [
                'order_id' =>   'required|exists:provisional_sticker_orders,id',
                'printed' =>  'required|integer',
            ],
            "messages" => [

                'printed.required' => 'El campo cantidad de impresos es requerido',
                'printed.integer' => 'El campo cantidad de impresos es de tipo numérico',

                'order_id.required' => 'El campo orden de impresión es requerido',
                'order_id.exists' => 'El campo orden de impresión no acerta ningún registro existente',
            ]
        ];

        try {

            $validator = Validator::make($request->all(), $validation['rules'], $validation['messages']);

            if ($validator->fails()) {
                return response()->json(['response' => ['errors' => $validator->errors()->all() ], 'message' => 'Bad Request'], 400);
            } 

            $order = ProvisionalStickerOrder::find($id);

            $order->printed = $order->printed + $request->printed;
            $order->save();

            return response()->json(['message' => 'Success', 'response' => ['data'=>$order, "errors" => []],], 200);

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
        //
    }
}
