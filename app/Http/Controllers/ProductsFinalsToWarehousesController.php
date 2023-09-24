<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductsFinalsToWarehouse;
use App\Models\ProductsFinal;
use App\Models\Transaction;
use App\Models\GridboxNew;

class ProductsFinalsToWarehousesController extends Controller
{

    public function __construct() {
        $this->middleware('can:product_final.index')->only('index');
        $this->middleware('can:products_finals_to_warehouses.enter_inventory')->only('enter_inventory');
        $this->middleware('can:products_finals_to_warehouses.show')->only('show');
        $this->middleware('can:products_finals_to_warehouses.update')->only('update');
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
                ["field" => "products_finals_to_warehouses.id"],
                ["field" => "number_control", "conditions" => "products_finals_to_warehouses.number_control"],
                ["field" => "product_final", "conditions" => "CONCAT(products_finals.name, ' ', products_finals.presentation, ' - ', products_finals.type)"],
                ["field" => "date", "conditions" => "products_finals_to_warehouses.date"],
                ["field" => "work_area", "conditions" => "products_finals_to_warehouses.work_area"],
                ["field" => "origin", "conditions" => "products_finals_to_warehouses.origin"],
                ["field" => "destination", "conditions" => "products_finals_to_warehouses.destination"],
                ["field" => "quantity", "conditions" => "CONCAT(FORMAT(products_finals_to_warehouses.quantity, 2), ' Kg')"],
                ["field" => "description", "conditions" => "products_finals_to_warehouses.description"],
                ["field" => "guide_sunagro", "conditions" => "products_finals_to_warehouses.guide_sunagro"],
                ["field" => "state", "conditions" => "products_finals_to_warehouses_states.name"],
                ["field" => "products_finals_to_warehouses.created_at"],
                ["field" => "products_finals_to_warehouses.updated_at"]
            ];

               #establezco los joins necesarios
               $params["join"] = [
                [ "type" => "inner", "join" => ["products_finals", "products_finals.id", "=", "products_finals_to_warehouses.product_final_id"] ],
                [ "type" => "inner", "join" => ["products_finals_to_warehouses_states", "products_finals_to_warehouses_states.id", "=", "products_finals_to_warehouses.state_id"] ]
            ];
            
            # Obteniendo la lista
            $products_finals_to_warehouses = GridboxNew::pagination("products_finals_to_warehouses", $params, false, $request);
            return response()->json($products_finals_to_warehouses);
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
            $order = ProductsFinalsToWarehouse::findOrFail($id);
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
     * put the product into inventory
     * 
     *@param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enter_inventory(Request $request, $id)
    {
        try {
            \DB::beginTransaction();

            $order = ProductsFinalsToWarehouse::findOrFail($id);

            if($order->state_id != 1)
                throw new \Exception('Esta orden ya fue ingresada al inventario');

            $product_final = ProductsFinal::findOrFail($order->product_final_id);

            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => false,
                'quantity_after' => $product_final->stock + $order->quantity,
                'quantity_before' => $product_final->stock,
                'quantity' => $order->quantity,
                'module' => 'Productos Terminados',
                'observation' => 'Se ingresó ' . $product_final->name
            ]);
            $transaction->save();

            $product_final->stock = $product_final->stock + $order->quantity;
            $product_final->save();

            $order->state_id = 2;
            $order->save();

            \DB::commit();
            return response()->json("Orden ingresada Correctamente", 202);

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

            if($request->quantity < 0 || empty($request->quantity) ) throw new \Exception("Cantidad de Producto Final ingresada no es correcta");
            if( empty($request->number_control) ) throw new \Exception('Debe ingresar el numero de control de la orden');
            if( empty($request->date) ) throw new \Exception('Debe ingresar la fecha del ingreso');
            if( empty($request->work_area) ) throw new \Exception('Debe ingresar el area de trabajo');
            if( empty($request->origin) ) throw new \Exception('Debe ingresar el origen');
            if( empty($request->destination) ) throw new \Exception('Debe ingresar el destino del producto');
            if( empty($request->guide_sunagro) ) throw new \Exception('Debe ingresar el Nº de Guia Sunagro');

            $order = ProductsFinalsToWarehouse::findOrFail($id);

            if($order->state_id != 1)
                throw new \Exception('Esta orden ya fue entregada al Almacen');

            $order->quantity = $request->quantity;
            $order->number_control = $request->number_control;
            $order->date = $request->date;
            $order->work_area = $request->work_area;
            $order->origin = $request->origin;
            $order->destination = $request->destination;
            $order->description = $request->description;
            $order->guide_sunagro = $request->guide_sunagro;
            $order->save();

            return response()->json('Orden actualizada correctamente', 202);

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
