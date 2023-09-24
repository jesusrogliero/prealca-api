<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DispatchesItem;
use App\Models\Dispatch;
use App\Models\GridboxNew;

class DispatchesItemsController extends Controller
{

    public function __construct() {
        $this->middleware('can:dispatch_item.index')->only('index');
        $this->middleware('can:dispatch_item.store')->only('store');
        $this->middleware('can:dispatch_item.show')->only('show');
        $this->middleware('can:dispatch_item.update')->only('update');
        $this->middleware('can:dispatch_item.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $dispatch_id)
    {
        try {
            $params = $request->all();

            #establezco los campos a mostrar
            $params["select"] = [
                ["field" => "dispatches_items.id"],
                ["field" => "product_final", "conditions" => "products_finals.name"],
                ["field" => "quantity", "conditions" => "CONCAT(FORMAT(dispatches_items.quantity, 2), ' Kg')"],
                ["field" => "dispatches_items.created_at"],
                ["field" => "dispatches_items.updated_at"]
            ];

           #establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["products_finals", "products_finals.id", "=", "dispatches_items.product_final_id"] ],
            ];

             #establezco los joins necesarios
             $params['where'] = [['dispatches_items.dispatch_id', '=', $dispatch_id]];
            
            # Obteniendo la lista
            $dispatches_items = GridboxNew::pagination("dispatches_items", $params, false, $request);
            return response()->json($dispatches_items);
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

            if( empty($request->product_final_id)) throw new \Exception("Debe seleccionar el producto terminado", 1);
           if($request->quantity <= 1) throw new \Exception("Debe ingresar la cantidad a despachar", 1);
           
            $dispatch = Dispatch::findOrFail($request->dispatch_id);

            if($dispatch->state_id != 1)
                throw new \Exception("Esta Orden ya fue procesada", 1);

            $item = new DispatchesItem();
            $item->product_final_id = $request->product_final_id;
            $item->quantity = $request->quantity;
            $item->dispatch_id = $dispatch->id;
            $item->save();

            // ajusto el despacho
            $dispatch->total = $dispatch->total + $item->quantity;
            $dispatch->save();
            
            \DB::commit();
            return response()->json('Producto Agregado Correctamente', 201);

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
            $dispatch_item = DispatchesItem::findOrFail($id);
            return response()->json($dispatch_item);

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

            if( empty($request->product_final_id)) throw new \Exception("Debe seleccionar el producto terminado", 1);
            if($request->quantity <= 1) throw new \Exception("Debe ingresar la cantidad a despachar", 1);
            
            $item = DispatchesItem::findOrFail($id);
            $dispatch = Dispatch::findOrFail($item->dispatch_id);
            
            // retiro la cantidad vieja
            $dispatch->total = $dispatch->total - $item->quantity;

            if($dispatch->state_id != 1)
                throw new \Exception("Esta Orden ya fue procesada", 1);

            $item->product_final_id = $request->product_final_id;
            $item->quantity = $request->quantity;
            $item->save();

            // ajusto el despacho
            $dispatch->total = $dispatch->total + $item->quantity;
            $dispatch->save();
            
            \DB::commit();
            return response()->json('Producto Actualizado Correctamente', 202);

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
            \DB::beginTransaction();

            $item = DispatchesItem::findOrFail($id);
            $dispatch = Dispatch::findOrFail($item->dispatch_id);
            
            if($dispatch->state_id != 1)
                throw new \Exception("Esta Orden ya fue procesada", 1);

            $item->delete();

            // ajusto el despacho
            $dispatch->total = $dispatch->total - $item->quantity;
            $dispatch->save();
            
            \DB::commit();
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
