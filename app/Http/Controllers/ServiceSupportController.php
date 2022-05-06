<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storeservice_supportRequest;
use App\Http\Requests\Updateservice_supportRequest;
use App\Models\service_support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\QueryException;
use DateTime;

class ServiceSupportController extends Controller
{
    private $client;

    public function __construct(){
        if(Route::getCurrentRoute()){
            $route = Route::getCurrentRoute()->uri();
            $this->client = ( preg_match("/api\//",$route)) ? "app" : "web";
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
            $service = DB::table('service_supports')
                ->join('parkings','service_supports.parkings_id','parkings.id')
                ->select('parkings.id AS parking_id',
                                 'parkings.name AS parking_name',
                                 'service_supports.id AS identificador',
                                 'service_supports.title AS title',
                                 'service_supports.description AS description',
                                 'service_supports.status AS status',
                                 'service_supports.created_at AS created_at'
                )->get()->toArray();
            return response()->json(['message' => 'Success', 'response' => ['data' => $service, 'errors' => [] ] ],200);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['message' => 'Internal Error', 'response' => ["errors" => [$th->getMessage()]]], 500);
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
     * @param  \App\Http\Requests\Storeservice_supportRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Storeservice_supportRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\service_support  $service_support
     * @return \Illuminate\Http\Response
     */
    public function show(service_support $service_support)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\service_support  $service_support
     * @return \Illuminate\Http\Response
     */
    public function edit(service_support $service_support)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Updateservice_supportRequest  $request
     * @param  \App\Models\service_support  $service_support
     * @return \Illuminate\Http\Response
     */
    public function update(Updateservice_supportRequest $request, service_support $service_support)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\service_support  $service_support
     * @return \Illuminate\Http\Response
     */
    public function destroy(service_support $service_support)
    {
        //
    }
}
