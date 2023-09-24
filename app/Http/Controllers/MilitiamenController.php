<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridBox;
use App\Models\Militiaman;

class MilitiamenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $params = $request->all();

            #establezco los campos a mostrar
            $params["select"] = [
                ["field" => "militiamen.id"],
                ["field" => "cedula", "conditions" => "CONCAT('V ', FORMAT(militiamen.cedula,0))"],
                ["field" => "name", "conditions" => "militiamen.name"],
                ["field" => "lastname", "conditions" => "militiamen.lastname"],
                ["field" => "location", "conditions" => "militiamen.location"],
                ["field" => "militiamen.created_at"],
                ["field" => "militiamen.updated_at"]
            ];
                        
            # Obteniendo la lista
            $militiamen = Gridbox::pagination("militiamen", $params, false, $request);
            return response()->json($militiamen);
        } catch(\Exception $e) {
            \Log::info("Error  ({$e->getCode()}):  {$e->getMessage()}  in {$e->getFile()} line {$e->getLine()}");
            return \Response::json([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 422);
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
        try {

            if( empty($request->name) ) throw new \Exception("Debes ingresar los nombres", 1);
            if( empty($request->lastname) ) throw new \Exception("Debes ingresar los apellidos", 1);
            if( empty($request->location) ) throw new \Exception("Debes ingresar la localizacion", 1);
            if( empty($request->cedula) ) throw new \Exception("Debes ingresar la cedula", 1);

           $new_milit = new Militiaman();

           $new_milit->name = $request->name;
           $new_milit->lastname = $request->lastname;
           $new_milit->location = $request->location;
           $new_milit->cedula = $request->cedula;
        
           $new_milit->save();

           return response()->json('Registrado Correctamente', 201);

        } catch(\Exception $e) {
            \Log::info("Error  ({$e->getCode()}):  {$e->getMessage()}  in {$e->getFile()} line {$e->getLine()}");
            return \Response::json([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 422);
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
        try {

           $milit = Militiaman::findOrFail($id);
           return response()->json($milit, 200);

        } catch(\Exception $e) {
            \Log::info("Error  ({$e->getCode()}):  {$e->getMessage()}  in {$e->getFile()} line {$e->getLine()}");
            return \Response::json([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 422);
        }

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
        
        try {

            if( empty($request->name) ) throw new \Exception("Debes ingresar los nombres", 1);
            if( empty($request->lastname) ) throw new \Exception("Debes ingresar los apellidos", 1);
            if( empty($request->location) ) throw new \Exception("Debes ingresar la localizacion", 1);
            if( empty($request->cedula) ) throw new \Exception("Debes ingresar la cedula", 1);

            $milit = Militiaman::findOrFail($id);
            
            # actualizo los datos
            $milit->name = $request->name;
            $milit->lastname = $request->lastname;
            $milit->location = $request->location;
            $milit->cedula = $request->cedula;

            $milit->save();

            return response()->json('Actualizado Correctamente', 202);

        } catch(\Exception $e) {
            \Log::info("Error  ({$e->getCode()}):  {$e->getMessage()}  in {$e->getFile()} line {$e->getLine()}");
            return \Response::json([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 422);
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
        try {

            $milit = Militiaman::findOrFail($id);
            $milit->delete();
            return response()->json(null, 204);

        } catch(\Exception $e) {
            \Log::info("Error  ({$e->getCode()}):  {$e->getMessage()}  in {$e->getFile()} line {$e->getLine()}");
            return \Response::json([
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 422);
        }
        
    }
}
