<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\LossProduction;
use App\Models\LossProductionsItem;

class LossProductionsItemsController extends Controller
{

    public function __construct() {
        $this->middleware('can:loss_productions_items.index')->only('get_items');
        $this->middleware('can:loss_productions_items.show')->only('show');
        $this->middleware('can:loss_productions_items.update')->only('update');
    } 

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_items(Request $request, $loss_production_id)
    {
        try {
            $params = $request->all();

            #establezco los campos a mostrar
            $params["select"] = [
                ["field" => "loss_productions_items.id"],
                ["field" => "primary_product", "conditions" => "primaries_products.name"],
                ["field" => "loss_quantity", "conditions" => "CONCAT(FORMAT(loss_productions_items.loss_quantity, 2), ' Kg')"],
                ["field" => "mixing_area_l1", "conditions" => "CONCAT(FORMAT(loss_productions_items.mixing_area_l1, 2), ' Kg')"],
                ["field" => "mixing_area_l2", "conditions" => "CONCAT(FORMAT(loss_productions_items.mixing_area_l2, 2), ' Kg')"],
                ["field" => "total", "conditions" => "CONCAT(FORMAT(loss_productions_items.total, 2), ' Kg')"],
                ["field" => "loss_productions_items.created_at"],
                ["field" => "loss_productions_items.updated_at"]
            ];

           #establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["primaries_products", "primaries_products.id", "=", "loss_productions_items.primary_product_id"] ],
            ];

            $params['where'] = [['loss_productions_items.loss_production_id', '=', $loss_production_id]];
            
            # Obteniendo la lista
            $loss_productions_items = GridboxNew::pagination("loss_productions_items", $params, false, $request);
            return response()->json($loss_productions_items);
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
           
            $loss_production = LossProductionsItem::findOrFail($id);
            return response()->json($loss_production);

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
            
           if($request->mixing_area_l1 < 0 ) throw new \Exception("La merma en el area de mezclado no es correcta", 1);
           if($request->mixing_area_l2 < 0) throw new \Exception("La merma en el area de mezclado no es correcta", 2);

           $loss_item = LossProductionsItem::findOrFail($id);

           $production_order = \DB::table('loss_productions')
           ->join('productions_consumptions', 'productions_consumptions.id', '=', 'loss_productions.consumption_id')
           ->join('productions_orders', 'productions_orders.id', '=', 'productions_consumptions.production_order_id')
           ->where('loss_productions.id', '=', $loss_item->loss_production_id)
           ->select('productions_orders.*')
           ->first();

            if($production_order->state_id != 1)
                throw new \Exception("Esta orden ya fue procesada!! No es posible editar los detalles de barrido");

           $loss_item->mixing_area_l1 = $request->mixing_area_l1;
           $loss_item->mixing_area_l2 = $request->mixing_area_l2;
           $loss_item->total = $loss_item->loss_quantity + ($request->mixing_area_l1 + $request->mixing_area_l2);
           $loss_item->save();

           $loss_production = LossProduction::findOrFail($loss_item->loss_production_id);
           $loss_production->total_recovered = $loss_production->total_recovered + $loss_item->total;
           $loss_production->save();
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

}
