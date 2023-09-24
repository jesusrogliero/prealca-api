<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionsConsumptionsItem;
use App\Models\ProductionsConsumption;
use App\Models\ProductionsOrder;
use App\Models\FormulasItem;
use App\Models\LossProduction;
use App\Models\LossProductionsItem;
use App\Models\GridboxNew;

class ProductionsConsumptionsItemsController extends Controller
{

    public function __construct() {
        $this->middleware('can:productions_consumptions_items.index')->only('get_consumption_items');
        $this->middleware('can:productions_consumptions_items.store')->only('store');
        $this->middleware('can:productions_consumptions_items.show')->only('show');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_consumption_items(Request $request, $consumption_id)
    {
        try {
            $params = $request->all();

            #establezco los campos a mostrar
            $params["select"] = [
                ["field" => "productions_consumptions_items.id"],
                ["field" => "primary_product", "conditions" => "primaries_products.name"],
                ["field" => "to_mixer", "conditions" => "CONCAT(FORMAT(productions_consumptions_items.to_mixer, 2), ' Kg')"],
                ["field" => "remainder1", "conditions" => "CONCAT(FORMAT(productions_consumptions_items.remainder1, 2), ' Kg')"],
                ["field" => "remainder2", "conditions" => "CONCAT(FORMAT(productions_consumptions_items.remainder2, 2), ' Kg')"],
                ["field" => "consumption_production", "conditions" => "CONCAT(FORMAT(productions_consumptions_items.consumption_production, 2), ' Kg')"],
                ["field" => "consumption_percentage", "conditions" => "CONCAT(productions_consumptions_items.consumption_percentage, ' %')"],
                ["field" => "theoretical_consumption", "conditions" => "CONCAT(FORMAT(productions_consumptions_items.theoretical_consumption, 2), ' Kg')"],
                ["field" => "difference", "conditions" => "CONCAT(FORMAT((productions_consumptions_items.consumption_production - productions_consumptions_items.theoretical_consumption), 2), ' Kg')"],
                ["field" => "productions_consumptions_items.created_at"],
                ["field" => "productions_consumptions_items.updated_at"]
            ];

           #establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["primaries_products", "primaries_products.id", "=", "productions_consumptions_items.primary_product_id"] ],
            ];

            $params['where'] = [['productions_consumptions_items.production_consumption_id', '=', $consumption_id]];
            
            # Obteniendo la lista
            $productions_consumptions_items = GridboxNew::pagination("productions_consumptions_items", $params, false, $request);
            return response()->json($productions_consumptions_items);
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
     * I generate the default items for production consumption
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function generate_items($consumption, $formula_id)
    {
        
            $formulas_items = FormulasItem::where('formula_id', $formula_id)->get();

            foreach ($formulas_items as $formula_item) {
                $consumption_production_item = new ProductionsConsumptionsItem();
                $consumption_production_item->production_consumption_id = $consumption->id;
                $consumption_production_item->primary_product_id = $formula_item->primary_product_id;
                $consumption_production_item->to_mixer = 0;
                $consumption_production_item->remainder1 = 0;
                $consumption_production_item->remainder2 = 0;
                $consumption_production_item->consumption_production = 0;
                $consumption_production_item->consumption_percentage = 0;
                $consumption_production_item->theoretical_consumption = $consumption->nro_batch * $formula_item->quantity;
                $consumption_production_item->save();

                $consumption->total_production = $consumption->total_production + $consumption_production_item->theoretical_consumption;
                $consumption->save();
            }
    }

    /**
     * I generate the default items for production consumption
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function ajust_items($consumption, $formula_id)
    {
            $formulas_items = FormulasItem::find($formula_id)->get();

            foreach ($formulas_items as $formula_item) {
                $item = ProductionsConsumptionsItem::where([
                    'primary_product_id' => $formula_item->primary_product_id,
                    'production_consumption_id' => $consumption->id
                ])->first();

                $item->theoretical_consumption = $consumption->nro_batch * $formula_item->quantity;
                $item->save();

                $consumption->total_production = $consumption->total_production + $item->theoretical_consumption;
            }
            $consumption->save();
            self::generate_percentage_items($consumption->id);
            
    }


    /**
     * I generate the default items for production consumption
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private static function generate_percentage_items($consumption_id)
    {
        
        $consumption = ProductionsConsumption::findOrFail($consumption_id);
        $consumption_items = ProductionsConsumptionsItem::where('production_consumption_id', $consumption_id)->get();

        foreach ($consumption_items as $consumption_item) {
            
            if($consumption->consumption_production > 0)
                $consumption_item->consumption_percentage = ($consumption_item->consumption_production * 100) / $consumption->consumption_production;
            
                $consumption_item->save();
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
        try{  
            $consumption = ProductionsConsumptionsItem::findOrFail($id);
            return response()->json($consumption);

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


    private static function ajust_loss($consumption_id) {
        $loss_production = LossProduction::where('consumption_id', $consumption_id)->first();

        if( !empty($loss_production) ) {

            $loss_production_items = LossProductionsItem::where('loss_production_id', $loss_production->id )->get();
            $loss_production->total_recovered = 0;
    
            foreach($loss_production_items as $loss_item) {
    
                $consumption_item = ProductionsConsumptionsItem::where([
                    'production_consumption_id' => $consumption_id,
                    'primary_product_id' => $loss_item->primary_product_id
                ] )->first();
    
                $loss_production->total_recovered = $loss_production->total_recovered - $loss_item->total;
    
                $loss_item->loss_quantity = ( $consumption_item->consumption_percentage * $loss_production->packing_area ) / 100;
                $loss_item->total = $loss_item->mixing_area_l1 + $loss_item->mixing_area_l2 + $loss_item->loss_quantity;
                $loss_item->save();
                
                $loss_production->total_recovered = $loss_production->total_recovered + $loss_item->total;
                
            }
            $loss_production->save();
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
        try{  
            \DB::beginTransaction();
         
            if(empty($request->to_mixer)) throw new \Exception("Debe ingresar la cantidad enviada al mezclador");
            if($request->remainder1 < 0) throw new \Exception("El remanente de la linea 1 es incorrecto");
            if($request->remainder2 < 0) throw new \Exception("El remanente de la linea 1 es incorrecto");
            
            

            $consumption_item = ProductionsConsumptionsItem::findOrFail($id);
            $consumption = ProductionsConsumption::findOrFail($consumption_item->production_consumption_id);

            $production_order = ProductionsOrder::findOrFail($consumption->production_order_id);

            if($production_order->state_id != 1)
                throw new \Exception('No es posible modificar una orden procesada');
            
            
            $consumption->consumption_production = $consumption->consumption_production - $consumption_item->consumption_production;
            $consumption->total_production = $consumption->total_production - $consumption_item->theoretical_consumption;

            $consumption_item->to_mixer = $request->to_mixer;
            $consumption_item->remainder1 = $request->remainder1;
            $consumption_item->remainder2 = $request->remainder2;

            $consumption_item->consumption_production = $request->to_mixer - ($request->remainder1 + $request->remainder2);
            
            $consumption->consumption_production = $consumption->consumption_production + $consumption_item->consumption_production;
            $consumption->total_production = $consumption->total_production +   $consumption_item->theoretical_consumption;
            
            $consumption_item->save();
            $consumption->save();

            self::generate_percentage_items($consumption->id);
            self::ajust_loss($consumption->id);

            \DB::commit();
            return response()->json('Guardado Correctamente', 202);

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
