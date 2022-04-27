<?php

namespace App\Http\Controllers;

use App\Models\UserApp;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserAppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(UserApp::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return response()->json($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            UserApp::create([
                'name' => $request->name,
                'password' => $request->password,
                'active' => $request->active,
                'users_id' => 1
            ]);
            return response()->json(['response' => 'Guardado Exitosamente', 'status' => 1]);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => 'Error al Guardar', 'status' => 2]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\UserApp  $userApp
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    public function showUserApp(Request $request)
    {
        try {
            $data = UserApp::where([['name', '=', $request->name], ['password', '=', $request->password]])->first();
            if (!$data) {
                return response()->json(['response' => 'Usuario no registrado en la base de datos', 'status' => 2]);
            } else if ($data->active == 2) {
                return response()->json(['response' => 'Usuario inactivo', 'status' => 2]);
            } else {
                return response()->json(['response' => $data, 'status' => 1]);
            }
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => 'Error SQL', 'status' => 2]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\UserApp  $userApp
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(UserApp::whereId($id)->first());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\UserApp  $userApp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $type = UserApp::findOrFail($request->id);
            $type->name = $request->name;
            $type->password = $request->password;
            $type->active = $request->active;
            $type->save();
            return response()->json(['response' => 'Actualizado Exitosamente', 'status' => 1]);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => 'Error al Actualizar', 'status' => 2]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\UserApp  $userApp
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $type = UserApp::findOrFail($id);
            $type->delete();
            return response()->json(['response' => 'Eliminado Exitosamente', 'status' => 1]);
        } catch (QueryException $th) {
            Log::emergency($th);
            return response()->json(['response' => 'Error al Eliminar', 'status' => 2]);
        }
    }
}
