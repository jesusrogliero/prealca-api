<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConsumptionsSuppliesMinor;
use App\Models\ProductionsConsumption;
use App\Models\ProductionsOrder;
use App\Models\SuppliesMinor;

class ConsumptionsSuppliesMinorsController extends Controller
{

    public function __construct() {
        $this->middleware('can:consumptions_supplies_minors.index')->only('index');
        $this->middleware('can:consumptions_supplies_minors.store')->only('store');
        $this->middleware('can:consumptions_supplies_minors.show')->only('show');
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
        try{  

            if(empty($request->consumption_id)) throw new \Exception("No se ha establecido un cosumo de producción", 1);
            if(empty($request->supply_minor_id)) throw new \Exception("Debes seleccionar el insumo menor antes guardar", 1);
            if($request->number_packages < 0) throw new \Exception("La cantidad de empaques es incorrecta", 1);

            $consumption_supply = ConsumptionsSuppliesMinor::where('consumption_id', $request->consumption_id)->first();
            $consumption = ProductionsConsumption::findOrFail($request->consumption_id);
            $supply_minor = SuppliesMinor::findOrFail($request->supply_minor_id);

            $production_order = ProductionsOrder::findOrFail($consumption->production_order_id);
            
            if( $production_order->state_id != 1 )
                throw new \Exception('No es posible modificar una orden procesada');


            if( empty($consumption_supply) ) {
                $consumption_supply = new ConsumptionsSuppliesMinor();
                $consumption_supply->consumption_id = $request->consumption_id;
            }

            $consumption_supply->supply_minor_id = $request->supply_minor_id;
            $consumption_supply->number_packages = $request->number_packages;
            $consumption_supply->consumption = $request->number_packages * $supply_minor->consumption_weight_package;
            $consumption_supply->consumption_bags = $request->number_packages / $supply_minor->unid;
            $consumption_supply->envoplast_consumption = $consumption->total_production / 960;
            $consumption_supply->save();

            return response()->json('Consumo Guardado Correctamente', 201);

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
    public function show($consumption_id)
    {
        try{  
            $consumption_supply = \DB::table('consumptions_supplies_minors')
            ->where('consumption_id', '=', $consumption_id)
            ->first();
            return response()->json($consumption_supply);

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
