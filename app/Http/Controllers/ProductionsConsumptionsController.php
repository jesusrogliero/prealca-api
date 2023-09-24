<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionsOrder;
use App\Models\ProductionsConsumption;
use App\Models\ProductionsConsumptionsItem;
use App\Http\Controllers\ProductionsConsumptionsItemsController as ConsumptionsItems;
use App\Models\ConsumptionsSuppliesMinor;
use App\Models\PrimariesProduct;
use App\Models\SuppliesMinor;
use App\Models\ProductsFinalsToWarehouse;
use App\Models\LossProduction;
use App\Models\Transaction;

class ProductionsConsumptionsController extends Controller
{

    public function __construct() {
        $this->middleware('can:productions_consumptions.store')->only('store');
        $this->middleware('can:productions_consumptions.show')->only('show');
        $this->middleware('can:productions_consumptions.approve_order')->only('approve');
       $this->middleware('can:productions_consumptions.get_consumptions_details')->only('get_consumptions_details');
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

            if(empty($request->production_order_id)) throw new \Exception("La orden de Produccion no ha sido recibida", 1);
            if(empty($request->nro_batch)) throw new \Exception("Antes de Generar por favor ingrese el numro de batch realizados", 1);

            $production_order = ProductionsOrder::findOrFail($request->production_order_id);

            if($production_order->state_id != 1)
                throw new \Exception('No es posible modificar una orden ya procesada');

            $consumption = null;

            print_r($request->consumption_id);

            if(empty($request->consumption_id)) {
               
                $consumption = ProductionsConsumption::firstWhere('production_order_id', $request->production_order_id);

                $consumption = new ProductionsConsumption();
                $consumption->production_order_id = $request->production_order_id;
                $consumption->total_production = 0;
                $consumption->consumption_production = 0;
                $consumption->nro_batch = $request->nro_batch;
                $consumption->save();

                ConsumptionsItems::generate_items($consumption, $production_order->formula_id);
            
            } else {

                $consumption = ProductionsConsumption::findOrFail($request->consumption_id);
                $consumption->nro_batch = $request->nro_batch;
                $consumption->total_production = 0;

                ConsumptionsItems::ajust_items($consumption, $production_order->formula_id);
            }



            \DB::commit();
            return response()->json(['message' => 'Orden generada correctamente', 'consumption_id' => $consumption->id], 201);
           
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
     * Apply changes to inventory in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request)
    {
        try {
            \DB::beginTransaction();

            $production_order = ProductionsOrder::findOrFail($request->production_order_id);

            if( $production_order->state_id != 1 )
                throw new \Exception('Esta orden ya ha sido procesada');
            
            $consumption = ProductionsConsumption::where('production_order_id', $production_order->id)->first();
            $consumption_items = ProductionsConsumptionsItem::where('production_consumption_id', $consumption->id)->get();
            $consumption_supply_minor = ConsumptionsSuppliesMinor::where('consumption_id', $consumption->id)->first();
        
            if( empty($consumption_supply_minor) )
                throw new \Exception('No has establecido el cosumo de los empaques');
          
            $supply_minor = SuppliesMinor::findOrFail($consumption_supply_minor->supply_minor_id);
            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => false,
                'quantity_after' => $supply_minor->stock - $consumption_supply_minor->consumption,
                'quantity_before' => $supply_minor->stock,
                'quantity' => $consumption_supply_minor->consumption,
                'module' => 'Insumo menor',
                'observation' => 'Se modificó ' . $supply_minor->name
            ]);
            $transaction->save();

            if($supply_minor->stock < $consumption_supply_minor->consumption) {
                throw new \Exception('No hay suficiente '. $supply_minor->name . ' Dentro del inventario');
            }
            $supply_minor->stock = $supply_minor->stock - $consumption_supply_minor->consumption;
            $supply_minor->save();
            

            $big_bags = SuppliesMinor::findOrFail(10);
            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => false,
                'quantity_after' => $big_bags->stock - $consumption_supply_minor->consumption_bags,
                'quantity_before' => $big_bags->stock,
                'quantity' => $consumption_supply_minor->consumption_bags,
                'module' => 'Insumo menor',
                'observation' => 'Se modificó ' . $big_bags->name
            ]);
            $transaction->save();
            
            if($big_bags->stock < $consumption_supply_minor->consumption_bags) {
                throw new \Exception('No hay suficiente '. $big_bags->name . ' Dentro del inventario');
            }
            $big_bags->stock = $big_bags->stock - $consumption_supply_minor->consumption_bags;
            $big_bags->save();

            
            $envoplast = SuppliesMinor::findOrFail(11);
            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => false,
                'quantity_after' => $envoplast->stock - $consumption_supply_minor->envoplast_consumption,
                'quantity_before' => $envoplast->stock,
                'quantity' => $consumption_supply_minor->envoplast_consumption,
                'module' => 'Insumo menor',
                'observation' => 'Se modificó ' . $envoplast->name
            ]);
            $transaction->save();

            if($envoplast->stock < $consumption_supply_minor->envoplast_consumption) {
                throw new \Exception('No hay suficiente '. $envoplast->name . ' Dentro del inventario');
            }
            $envoplast->stock = $envoplast->stock - $consumption_supply_minor->envoplast_consumption;
            $envoplast->save();


            $order_to_warehouse = new ProductsFinalsToWarehouse([
                'product_final_id' => $production_order->product_final_id,
                'quantity' => 0,
                'state_id' => 1,
                'production_order_id' => $production_order->id
            ]);
            $order_to_warehouse->save();
           
            foreach($consumption_items as $item) {
                
                $primary_product = PrimariesProduct::findOrFail($item->primary_product_id);
                
                if($primary_product->stock < $item->to_mixer)
                    throw new \Exception('No hay suficiente '. $primary_product->name . ' Dentro del inventario');

                $transaction = new Transaction([
                    'user_id' => $request->user()->id,
                    'action' => false,
                    'quantity_after' => floatval($primary_product->stock) - floatval($item->to_mixer),
                    'quantity_before' => $primary_product->stock,
                    'quantity' => floatval($item->to_mixer),
                    'module' => 'Productos Primarios',
                    'observation' => 'Se modificó ' . $primary_product->name
                ]);
                $transaction->save();
                  
                $primary_product->stock = floatval($primary_product->stock) - floatval($item->to_mixer);

                $primary_product->save();
                $primary_product = null;
            }
           
            $production_order->state_id = 2;
            $production_order->save();

            \DB::commit();
            return response()->json('Los cambios han sido reflejados en el inventario correctamente', 202);
           
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
            $consumption = \DB::table('productions_consumptions')
                ->where('productions_consumptions.production_order_id', $id)
                ->first();
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

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get_consumptions_details($production_order_id)
    {
        try {

            $production_order = ProductionsOrder::findOrFail($production_order_id);


            $select = 'select ';
            $select .= 'CONCAT(products_finals.name, " ", products_finals.presentation) as product_final_name, ';
            $select .= 'products_finals.type as product_final_type, ';
            $select .= "CONCAT(FORMAT(productions_orders.quantity, 2), ' Kg') as quantity, ";
            $select .= "productions_orders_states.name as production_order_state, ";
            $select .= "productions_orders.issued_by, ";
            $select .= "productions_orders.created_at ";
            $select .= "FROM productions_orders ";
            $select .= "INNER JOIN products_finals on products_finals.id = productions_orders.product_final_id ";
            $select .= "INNER JOIN formulas on formulas.id = productions_orders.formula_id ";
            $select .= "INNER JOIN productions_orders_states on productions_orders_states.id = productions_orders.state_id ";
            $select .= "WHERE productions_orders.id = " . $production_order_id;
            $production_order = \DB::select($select);

            if(!empty($production_order))
                $production_order = $production_order[0];
            else
                $production_order = null;

            $consumption = \DB::table('productions_consumptions')
            ->select('productions_consumptions.*')
            ->where('production_order_id', '=', $production_order_id)
            ->first();

            $select = 'select ';
            $select .= 'primaries_products.name as primary_product, ';
            $select .= "CONCAT(FORMAT(productions_consumptions_items.to_mixer, 2), ' Kg') as to_mixer, ";
            $select .= "CONCAT(FORMAT(productions_consumptions_items.remainder1, 2), ' Kg') as remainder1, ";
            $select .= "CONCAT(FORMAT(productions_consumptions_items.remainder2, 2), ' Kg') as remainder2, ";
            $select .= "CONCAT(FORMAT(productions_consumptions_items.consumption_production, 2), ' Kg') as consumption_production, ";
            $select .= "CONCAT(FORMAT(productions_consumptions_items.consumption_percentage, 2), ' %') as consumption_percentage, ";
            $select .= "CONCAT(FORMAT(productions_consumptions_items.theoretical_consumption, 2), ' Kg') as theoretical_consumption, ";
            $select .= "CONCAT(FORMAT((productions_consumptions_items.consumption_production - productions_consumptions_items.theoretical_consumption), 2), ' Kg') as difference ";
            $select .= "FROM productions_consumptions_items ";
            $select .= "INNER JOIN primaries_products on primaries_products.id = productions_consumptions_items.primary_product_id ";
            $select .= "WHERE productions_consumptions_items.production_consumption_id = " . $consumption->id;
            $consumption_items = \DB::select($select);


            $select = 'select ';
            $select .= 'supplies_minors.name as supply_name, ';
            $select .= "CONCAT(FORMAT(consumptions_supplies_minors.number_packages, 2), ' UNID') as number_packages, ";
            $select .= "CONCAT(FORMAT(consumptions_supplies_minors.consumption, 2), ' Kg') as consumption, ";
            $select .= "CONCAT(FORMAT(consumptions_supplies_minors.consumption_bags, 2), ' Kg') as consumption_bags, ";
            $select .= "CONCAT(FORMAT(consumptions_supplies_minors.envoplast_consumption, 2), ' Kg') as envoplast_consumption ";
            $select .= "FROM consumptions_supplies_minors ";
            $select .= "INNER JOIN supplies_minors on supplies_minors.id = consumptions_supplies_minors.supply_minor_id ";
            $select .= "WHERE consumptions_supplies_minors.consumption_id = " . $consumption->id;
            $consumption_supply_minor = \DB::select($select);

            if(!empty($consumption_supply_minor))
                $consumption_supply_minor = $consumption_supply_minor[0];
            else
                $consumption_supply_minor = null;

            $loss_production = LossProduction::where('consumption_id', $consumption->id)->first();

            return response()->json([
                'production_order' => $production_order,
                'production_consumption' => $consumption,
                'production_consumption_items' => $consumption_items,
                'consumption_supply_minor' => $consumption_supply_minor,
                'loss_production' => $loss_production
            ]);
           
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
