<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LossProduction;
use App\Models\LossProductionsItem;
use App\Models\ProductionsConsumptionsItem;

class LossProductionsController extends Controller
{

    public function __construct() {
        $this->middleware('can:loss_productions.index')->only('index');
        $this->middleware('can:loss_productions.store')->only('store');
        $this->middleware('can:loss_productions.show')->only('show');
        $this->middleware('can:loss_productions.update')->only('update');
    } 


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

            if(empty($request->consumption_id)) throw new \Exception("Debe generar un consumo de producción antes de continuar", 1);
            if($request->packing_area < 0) throw new \Exception("Merma en Area de Empaque no es correcto", 1);
            if($request->hopper_auger < 0) throw new \Exception("Merma en Tornillo y Tolva no es correcto", 1);
            if($request->lab < 0) throw new \Exception("Cantidad de muestra de laboratorio no es correcta", 1);


            $production_order = \DB::table('productions_orders')->select('productions_orders.*')
            ->join('productions_consumptions', 'productions_consumptions.production_order_id', '=', 'productions_orders.id')
            ->where('productions_consumptions.id', '=', $request->consumption_id)
            ->first();

            if($production_order->state_id != 1)
                throw new \Exception("Esta orden ha sido procesada");
                
            $consumption_items = ProductionsConsumptionsItem::where('production_consumption_id', $request->consumption_id)->get();

            if(empty($consumption_items))
                throw new \Exception('No existe un consumo de producción');
            
            $loss_production = LossProduction::where('consumption_id', $request->consumption_id)->first();
            if( !empty($loss_production) )
                throw new \Exception('Ya existe un registro de merma');

            $new_loss_production = new LossProduction();
            $new_loss_production->consumption_id = $request->consumption_id;
            $new_loss_production->packing_area = $request->packing_area;
            $new_loss_production->lab = $request->lab;
            $new_loss_production->hopper_auger = $request->hopper_auger;
            $new_loss_production->total_recovered = 0;
            $new_loss_production->save();
           

            foreach($consumption_items as $item) {
                $new_loss_item = new LossProductionsItem(); 
                $new_loss_item->loss_production_id = $new_loss_production->id;
                $new_loss_item->primary_product_id = $item->primary_product_id;
                $new_loss_item->loss_quantity = ($item->consumption_percentage * $new_loss_production->packing_area) / 100;
                $new_loss_item->mixing_area_l1 = 0;
                $new_loss_item->mixing_area_l2 = 0;
                $new_loss_item->total = $new_loss_item->loss_quantity;
                $new_loss_item->save();

                $new_loss_production->total_recovered = $new_loss_production->total_recovered + $new_loss_item->loss_quantity;
            }
            $new_loss_production->save();
            
            \DB::commit();
            return response()->json('Registro de Merma Guardado Exitosamente', 201);
           
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
     * @param  int  $id --> (consumption_id)
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $loss_production = LossProduction::where('consumption_id', $id)->first();
            return response()->json($loss_production);
           
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
            
            if($request->packing_area < 0) throw new \Exception("Merma en Area de Empaque no es correcto", 1);
            if($request->hopper_auger < 0) throw new \Exception("Merma en Tornillo y Tolva no es correcto", 1);
            if($request->lab < 0) throw new \Exception("Cantidad de muestra de laboratorio no es correcta", 1);

            $loss_production = LossProduction::findOrFail($id);

            $production_order = \DB::table('loss_productions')
            ->join('productions_consumptions', 'productions_consumptions.id', '=', 'loss_productions.consumption_id')
            ->join('productions_orders', 'productions_orders.id', '=', 'productions_consumptions.production_order_id')
            ->where('loss_productions.id', '=', $loss_production->id)
            ->select('productions_orders.*')
            ->first();

            if($production_order->state_id != 1)
                throw new \Exception("Esta orden ha sido procesada");

           
            $loss_production->packing_area = $request->packing_area;
            $loss_production->hopper_auger = $request->hopper_auger;
            $loss_production->lab = $request->lab;

            $loss_production_items = LossProductionsItem::where('loss_production_id', $id)->get();

            foreach( $loss_production_items as $item ) {

                $loss_production->total_recovered = $loss_production->total_recovered - $item->total;

                $consumption_item = \DB::table('productions_consumptions_items')
                ->where('production_consumption_id', '=', $loss_production->consumption_id)
                ->where('primary_product_id', '=', $item->primary_product_id)
                ->first();

                if(empty($consumption_item)) 
                    throw new \Exception("Error Inesperado. No fue posible procesar los datos ingresados", 1);
                
                $item->loss_quantity = ($consumption_item->consumption_percentage * $loss_production->packing_area) / 100;
                $item->total = $item->loss_quantity + ($item->mixing_area_l1 + $item->mixing_area_l2);
                $item->save();

                $loss_production->total_recovered = $loss_production->total_recovered + $item->total;
            }
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
