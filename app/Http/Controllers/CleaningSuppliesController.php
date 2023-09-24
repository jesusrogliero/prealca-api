<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gridbox;
use App\Models\CleaningSupply;
use App\Models\Presentation;
use App\Models\Transaction;

class CleaningSuppliesController extends Controller
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
                ["field" => "cleaning_supplies.id"],
                ["field" => "name", "conditions" => "cleaning_supplies.name"],
                ["field" => "stock", "conditions" => "CONCAT( FORMAT(cleaning_supplies.stock, 2), ' ', presentations.name)"],
                ["field" => "presentation", "conditions" => "presentations.name"],
                ["field" => "description", "conditions" => "cleaning_supplies.description"],
                ["field" => "requirement", "conditions" => "cleaning_supplies.requirement"],
                ["field" => "cleaning_supplies.created_at"],
                ["field" => "cleaning_supplies.updated_at"]
            ];
            
            #establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["presentations", "presentations.id", "=", "cleaning_supplies.presentation_id"] ]
            ];

            # Obteniendo la lista
            $cleaning_supplies = Gridbox::pagination("cleaning_supplies", $params, false, $request);
            return response()->json($cleaning_supplies);
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
     *  Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show_presentations(Request $request)
    {
        try {
            $presentations = \DB::table('presentations')->select('*')->get();
            return response()->json($presentations);

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

            \DB::beginTransaction();

            if( empty($request->name ) )
                throw new \Exception("Debes ingresar el nombre del insumo", 1);
                
            if( empty($request->presentation_id ) )
                throw new \Exception("Debes seleciona la presentacion del producto", 1);

            if( empty($request->stock) )
                throw new \Exception("Debes ingresar la existecia del insumo", 1);
                
            if( $request->stock < 0 )
                throw new \Exception("La existencia del insumo no es correcta", 1);
            
            
            $new_clean_suply = new CleaningSupply();

            $new_clean_suply->name = $request->name;
            $new_clean_suply->presentation_id = $request->presentation_id;
            $new_clean_suply->stock = $request->stock;
            $new_clean_suply->description = $request->description;
            $new_clean_suply->requirement = $request->requirement;

            $new_clean_suply->save();

            new Transaction([
                'user_id' => $request->user_id,
                'action' => true,
                'module' => 'Insumos de Limpieza',
                'observation' => 'Nuevo Insumo de Limpieza'
            ]);

            \DB::commit();
            return response()->json('Registrado Correctamente', 201);

        } catch(\Exception $e) {
            \DB::rollback();
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
            $clean_suply = CleaningSupply::findOrFail($id);
            return response()->json($clean_suply);

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
            \DB::beginTransaction();

            if( empty($request->name ) )
                throw new \Exception("Debes ingresar el nombre del insumo", 1);
            
            if( empty($request->presentation_id ) )
                throw new \Exception("Debes seleciona la presentacion del producto", 1);

            if( empty($request->stock) )
                throw new \Exception("Debes ingresar la existecia del insumo", 1);
                
            if( $request->stock < 0 )
                throw new \Exception("La existencia del insumo no es correcta", 1);
        

            $clean_suply = CleaningSupply::findOrFail($id);

            $clean_suply->name = $request->name;
            $clean_suply->presentation_id = $request->presentation_id;
            $clean_suply->stock = $request->stock;
            $clean_suply->requirement = $request->requirement;
            $clean_suply->description = $request->description;

            $clean_suply->save();

            new Transaction([
                'user_id' => $request->user_id,
                'action' => true,
                'module' => 'Insumos de Limpieza',
                'observation' => 'Actualizado Insumo de Limpieza'
            ]);

            \DB::commit();
            return response()->json('Actualizado Correctamente', 202);

        } catch(\Exception $e) {
            \DB::rollback();
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

            $clean_suply = CleaningSupply::findOrFail($id);
            $clean_suply->delete();
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
