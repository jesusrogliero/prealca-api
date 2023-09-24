<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\NonconformingProduct;
use App\Models\PrimariesProduct;
use App\Models\Transaction;

class NonconformingProductsController extends Controller
{
    public function __construct() {
        $this->middleware('can:nonconforming_products.index')->only('index');
        $this->middleware('can:nonconforming_products.store')->only('store');
        $this->middleware('can:nonconforming_products.show')->only('show');
        $this->middleware('can:nonconforming_products.update')->only('update');
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
                ["field" => "nonconforming_products.id"],
                ["field" => "primary_product", "conditions" => "primaries_products.name"],
                ["field" => "quantity", "conditions" => "CONCAT(FORMAT(nonconforming_products.quantity, 2), ' KG')"],
                ["field" => "observation", "conditions" => "nonconforming_products.observation"],
                ["field" => "nonconforming_products.created_at"],
                ["field" => "nonconforming_products.updated_at"]
            ];

            
            #establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["primaries_products", "primaries_products.id", "=", "nonconforming_products.primary_product_id"] ]
            ];
            
            # Obteniendo la lista
            $nonconforming_products = GridboxNew::pagination("nonconforming_products", $params, false, $request);
            return response()->json($nonconforming_products);
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

            if($request->quantity < 0)
                throw new \Exception("La contidad ingresada es incorrecta");

            if( empty($request->primary_product_id) )
                throw new \Exception("Debe ingresar un producto primario");

            $primary_product = PrimariesProduct::findOrFail($request->primary_product_id);

            if($primary_product->stock < $request->quantity)
                throw new \Exception("No hay existencia suficiente de este producto en el inventario", 1);

            $new_pnc = new NonconformingProduct();
            $new_pnc->primary_product_id = $request->primary_product_id;
            $new_pnc->quantity = $request->quantity;
            $new_pnc->observation = $request->observation;
            $new_pnc->save();

            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => true,
                'quantity_after' => $request->quantity,
                'quantity_before' => 0,
                'quantity' => $request->quantity,
                'module' => 'productos primarios no conformes',
                'observation' => 'Se creó ' . $primary_product->name 
            ]);
            $transaction->save();

            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => false,
                'quantity_after' => $primary_product->stock - $request->quantity,
                'quantity_before' => $primary_product->stock,
                'quantity' => $request->quantity,
                'module' => 'Productos Primarios',
                'observation' => 'Se actualizó ' . $primary_product->name
            ]);
            $transaction->save();

            $primary_product->stock = $primary_product->stock - $request->quantity;
            $primary_product->save();

           \DB::commit();
           return response()->json('Registrado Correctamente', 201);

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
            $product_NC = NonconformingProduct::findOrFail($id);
            return response()->json($product_NC);

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
     * update the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $product_NC = NonconformingProduct::findOrFail($id);
            $product_NC->observation = $request->observation;
            $product_NC->save();

            return response()->json("Guardado Correctamente", 202);

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
