<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetailedStickerOrder;
use App\Models\Parking;
use App\Models\Bicy;

class DetailedStickerOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DetailedStickerOrder:://where(['detailed_sticker_orders.active'=>'1'])
            join('bicies', 'detailed_sticker_orders.bicies_id','bicies.id')
            ->join('bikers', 'bikers.id','bicies.bikers_id')
            ->orderBy('detailed_sticker_orders.active','desc')
            ->select('detailed_sticker_orders.*','bicies.code as bicy_code', 'bikers.document as biker_document')->get();
        $parkings = Parking::select('id','name','code')->get();
        $bicies = Bicy::select('id','code')->get();
        return response()->json(['message'=>'Success', 'response'=>['data'=>$data, 'indexes'=>['parkings'=>$parkings, 'bicies'=>$bicies], 'errors'=>[]]],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $bicy = Bicy::find($request->bicies_id);
        if(!$bicy){ return response()->json(['message'=>'Not Found', 'response'=>['errors'=>['Registro de bicicleta no encontrado']]],404);}

        $exists = DetailedStickerOrder::where(['bicies_id'=> $request->bicies_id, 'parkings_id' => $bicy->parkings_id,'active'=>1])->get();
        
        if($exists->count()){
            return response()->json(['message'=>'Bad Request', 'response'=>['errors'=>['La bicicleta ya tiene un pedido activo de sticker.']]],400);
        }

        $data = DetailedStickerOrder::create([
            'bicies_id' => $request->bicies_id,
            'parkings_id' => $bicy->parkings_id,
            'users_id' => $request->user()->id,
            'active' => '1',
        ]);

        return response()->json(['message'=>'Success', 'response'=>['data'=>$data, 'errors'=>[]]],201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ids = explode(',',$id);
        $data = DetailedStickerOrder::/* where(['detailed_sticker_orders.active'=>'1'])
            -> */whereIn('detailed_sticker_orders.id',$ids)
            ->join('bicies','bicies.id','detailed_sticker_orders.bicies_id')
            ->join('bikers', 'bicies.bikers_id', 'bikers.id')
            ->join('type_documents', 'bikers.type_documents_id', 'type_documents.id')
            ->join('type_bicies', 'bicies.type_bicies_id', 'type_bicies.id')

            ->select(
                'bicies.id as bicy_id','bicies.serial as serial','bicies.code','bicies.color','bicies.brand','type_bicies.name as bicy_type', 'bicies.tires as bicy_tires',
                'type_documents.code as biker_document_type', 'bikers.document as biker_document'
                ,'detailed_sticker_orders.users_id as requester_id','detailed_sticker_orders.id as order_id'
            )
        ->get();
        $parkings = Parking::where(['id'=>$id])->select('id','name','code')->get();
        return response()->json(['message'=>'Success', 'response'=>['data'=>$data, 'indexes'=>['parkings'=>$parkings], 'errors'=>[]]],200);

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
        $ids = explode(',',$id);
        $update = DetailedStickerOrder::whereIn('id',$ids)
            ->update(['active'=>'0']);

        return response()->json(['message'=>'Success', 'response'=>['data'=>[$update], 'indexes'=>[], 'errors'=>[]]],200);
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
