<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\ProductionsOrder;
use App\Models\ProductionsConsumptionsItem;
use App\Models\ProductionsConsumption;
use App\Models\ConsumptionsSuppliesMinor;
use App\Models\LossProduction;
use App\Models\LossProductionsItem;
use App\Models\Formula;

class ProductionsOrdersController extends Controller
{

    public function __construct() {
        $this->middleware('can:productions_orders.index')->only('index');
        $this->middleware('can:productions_orders.store')->only('store');
        $this->middleware('can:productions_orders.show')->only('show');
        $this->middleware('can:productions_orders.update')->only('update');
        $this->middleware('can:productions_orders.destroy')->only('destroy');
        
        $this->middleware('can:productions_orders.get_formula_with_production_order')
        ->only('get_formula_with_production_order');
    }

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
                ["field" => "productions_orders.id"],
                ["field" => "product_final", "conditions" => "CONCAT(products_finals.name, ' ', products_finals.presentation, ' - ', products_finals.type)"],
                ["field" => "formula", "conditions" => "CONCAT(formulas.name, ' ', formulas.quantity_batch, 'Kg')"],
                ["field" => "quantity", "conditions" => "CONCAT(FORMAT(productions_orders.quantity, 2), ' Kg')"],
                ["field" => "state", "conditions" => "productions_orders_states.name"],
                ["field" => "productions_orders.created_at"],
                ["field" => "productions_orders.updated_at"]
            ];

           #establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["products_finals", "productions_orders.product_final_id", "=", "products_finals.id"] ],
                [ "type" => "inner", "join" => ["formulas", "productions_orders.formula_id", "=", "formulas.id"] ],
                [ "type" => "inner", "join" => ["productions_orders_states", "productions_orders_states.id", "=", "productions_orders.state_id"] ],
            ];
            
            # Obteniendo la lista
            $productions_orders = GridboxNew::pagination("productions_orders", $params, false, $request);
            return response()->json($productions_orders);
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
        try{  
            if(empty($request->product_final_id)) throw new \Exception("Debes seleccionar el producto final", 1);
            if(empty($request->formula_id)) throw new \Exception("Debes selecciona una formula", 1);
            if(empty($request->quantity)) throw new \Exception("La cantidad a producir es requerida", 1);

            $user = $request->user();

            $new_order = new ProductionsOrder();
            $new_order->formula_id = $request->formula_id;
            $new_order->product_final_id = $request->product_final_id;
            $new_order->quantity = $request->quantity;
            $new_order->state_id = 1;
            $new_order->issued_by = $user->name . ' ' . $user->lastname;
            $new_order->save();

            return response()->json('Orden de Producción Creada Correctamente', 201);

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
        try{  
            $order = ProductionsOrder::findOrFail($id);
            return response()->json($order);

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
    public function get_formula_with_production_order($production_order_id)
    {
        try{  

            $formula = \DB::table('formulas')
            ->join('productions_orders', 'productions_orders.formula_id', '=', 'formulas.id')
            ->where('productions_orders.id', '=' , $production_order_id)
            ->select('formulas.*')
            ->first();

            $order = ProductionsOrder::findOrFail($production_order_id);

            return response()->json(['formula' => $formula, 'order' => $order]);

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
        try{  
            $order = ProductionsOrder::findOrFail($id);

            if($order->state_id != 1)
                throw new \Exception("No es posible actualizar esta orden de producción", 1);
                
            $order->formula_id = $request->formula_id;
            $order->quantity = $request->quantity;
            $order->product_final_id = $request->product_final_id;

            return response()->json('Orden Actualizada Correctamente', 202);

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
        try{  
            \DB::beginTransaction();

            $production_order = ProductionsOrder::findOrFail($id);

            if($production_order->state_id != 1)
                throw new \Exception("No es posible eliminar esta orden de producción", 1);

            $consumption_order = ProductionsConsumption::where('production_order_id', $production_order->id)->first();
            
            if( !empty($consumption_order) ) {
                $consumption_order_items = ProductionsConsumptionsItem::where('production_consumption_id', $consumption_order->id);
                $consumption_order_items->delete();

                $consumption_supply_minor = ConsumptionsSuppliesMinor::where('consumption_id', $consumption_order->id)->first();
                if( !empty($consumption_supply_minor) ) {
                    $consumption_supply_minor->delete();
                }
    
                $loss_production = LossProduction::where('consumption_id', $consumption_order->id)->first();
                if( !empty($loss_production) ) {
                    $loss_production_items = LossProductionsItem::where('loss_production_id', $loss_production->id);
                    $loss_production_items->delete();
                    $loss_production->delete();
                }
                $consumption_order->delete();
            }
            $production_order->delete();

            \DB::commit();
            return response()->json(null, 204);

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
