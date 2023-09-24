<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchasesOrder;
use App\Models\PurchasesOrdersItem;
use App\Models\GridboxNew;

class PurchasesOrdersItemsController extends Controller
{

    public function __construct() {
        $this->middleware('can:purchases_orders_items.index')->only('index');
        $this->middleware('can:purchases_orders_items.store')->only('store');
        $this->middleware('can:purchases_orders_items.show')->only('show');
        $this->middleware('can:purchases_orders_items.update')->only('update');
        $this->middleware('can:purchases_orders_items.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        try {
            $params = $request->all();

            #establezco los campos a mostrar
            $params["select"] = [
                ["field" => "purchases_orders_items.id"],
                ["field" => "primary_product", "conditions" => "primaries_products.name"],
                ["field" => "quantity", "conditions" => "CONCAT(FORMAT(purchases_orders_items.quantity, 2), ' KG')"],
                ["field" => "nonconform_quantity", "conditions" => "CONCAT( FORMAT(purchases_orders_items.nonconform_quantity, 2), ' KG')"],
                ["field" => "due_date", "conditions" => "purchases_orders_items.due_date"],
                ["field" => "nro_lote", "conditions" => "purchases_orders_items.nro_lote"],
                ["field" => "purchases_orders_items.created_at"],
                ["field" => "purchases_orders_items.updated_at"]
            ];

           #establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["primaries_products", "primaries_products.id", "=", "purchases_orders_items.primary_product_id"] ],
            ];

            $params['where'] = [['purchases_orders_items.purchase_order_id', '=', $id]];
            
            # Obteniendo la lista
            $purchases_orders_items = GridboxNew::pagination("purchases_orders_items", $params, false, $request);
            return response()->json($purchases_orders_items);
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

            if(empty($request->primary_product_id)) throw new \Exception("La materia prima es requerida", 1);
            if(empty($request->quantity)) throw new \Exception("La cantidad del Articulo es requerida", 1);
            if($request->quantity < 0) throw new \Exception("La cantidad del Articulo no es correcta", 1);
            if($request->nonconform_quantity < 0) throw new \Exception("La cantidad no conforme no es correcta", 1);
            
            $order = PurchasesOrder::findOrFail($request->purchase_order_id);

            if($order->state_id != 1)
                throw new \Exception('Esta orden ya fue Ingresada');

            $item = PurchasesOrdersItem::where([
                'primary_product_id' => $request->primary_product_id,
                'purchase_order_id' => $request->purchase_order_id
            ])->first();

            if(!empty($item))
                throw new \Exception('Este producto ya fue agregado a la orden');


            # Creo el articulo de la orden
            $new_item = new PurchasesOrdersItem();
            $new_item->primary_product_id = $request->primary_product_id;
            $new_item->quantity = $request->quantity;
            $new_item->due_date = $request->due_date;
            $new_item->purchase_order_id = $request->purchase_order_id;
            $new_item->nro_lote = $request->nro_lote;
            $new_item->nonconform_quantity = $request->nonconform_quantity;
            $new_item->save();

            # Ajusto la orden
            $order = PurchasesOrder::findOrFail($new_item->purchase_order_id);
            $order->total_products = $order->total_products + 1;
            $order->total_nonconforming = $order->total_nonconforming + $request->nonconform_quantity;
            $order->total_load = $order->total_load + $new_item->quantity;
            $order->save();

            \DB::commit();
            return response()->json('El Articulo Fue Guardado', 201);

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
    public function show(Request $request, $id)
    {
        try{
            $item = PurchasesOrdersItem::findOrFail($id);
            return response()->json($item);

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

            if(empty($request->primary_product_id)) throw new \Exception("La materia prima es requerida", 1);
            if(empty($request->quantity)) throw new \Exception("La cantidad del Articulo es requerida", 1);
            if($request->quantity < 0) throw new \Exception("La cantidad del Articulo no es valida", 1);
            if($request->nonconform_quantity < 0) throw new \Exception("La cantidad no conforme no es valida", 1);
            
            $item = PurchasesOrdersItem::findOrFail($id);
            $order = PurchasesOrder::findOrFail($item->purchase_order_id);

            if($order->state_id != 1)
                throw new \Exception('Esta orden ya fue Procesada');

            $order->total_load = $order->total_load - $item->quantity;
            $order->total_nonconforming = $order->total_nonconforming - $item->nonconform_quantity;

            $item->primary_product_id = $request->primary_product_id;
            $item->quantity = $request->quantity;
            $item->due_date = $request->due_date;
            $item->purchase_order_id = $request->purchase_order_id;
            $item->nro_lote = $request->nro_lote;
            $item->nonconform_quantity = $request->nonconform_quantity;
            $item->save();

            $order->total_load = $order->total_load + $item->quantity;
            $order->total_nonconforming = $order->total_nonconforming + $item->nonconform_quantity;
            $order->save();

            \DB::commit();
            return response()->json('El Articulo fue actualizado', 202);

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
            
            $item = PurchasesOrdersItem::findOrFail($id);
            $order = PurchasesOrder::findOrFail($item->purchase_order_id);

            if($order->state_id != 1)
                throw new \Exception('Esta ya fue procesada');

            $order->total_products = $order->total_load - $item->quantity;
            $order->total_products = $order->total_products - 1;
            $order->total_nonconforming = $order->total_nonconforming - $item->noconform_quantity;
            
            $item->delete();
            $order->save();

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
