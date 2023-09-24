<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MachineriesConsumable;
use App\Models\Gridbox;

class MachineriesConsumablesController extends Controller
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
                ["field" => "machineries_consumables.id"],
                ["field" => "name", "conditions" => "machineries_consumables.name"],
                ["field" => "stock", "conditions" => "machineries_consumables.stock"],
                ["field" => "description", "conditions" => "machineries_consumables.description"],
                ["field" => "requirement", "conditions" => "machineries_consumables.requirement"],
                ["field" => "machineries_consumables.created_at"],
                ["field" => "machineries_consumables.updated_at"]
            ];
            
            # Obteniendo la lista
            $machineries_consumables = Gridbox::pagination("machineries_consumables", $params, false, $request);
            return response()->json($machineries_consumables);
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
            $new_resource = new MachineriesConsumable();

            $new_resource->name = $request->name;
            $new_resource->stock = $request->stock;
            $new_resource->requirement = $request->requirement;
            $new_resource->description = $request->description;

            $new_resource->save();

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

            $machinerie_consumable = MachineriesConsumable::findOrFail($id);
            return response()->json($machinerie_consumable);

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
            
            $consumable = MachineriesConsumable::findOrFail($id);

            $consumable->name = $request->name;
            $consumable->stock = $request->stock;
            $consumable->requirement = $request->requirement;
            $consumable->description = $request->description;

            $consumable->save();

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
            
            $consumable = MachineriesConsumable::findOrFail($id);
            $consumable->delete();

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
