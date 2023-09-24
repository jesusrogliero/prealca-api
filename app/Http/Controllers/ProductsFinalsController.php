<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\ProductsFinal;
use App\Models\Transaction;

class ProductsFinalsController extends Controller
{
    
    public function __construct() {
        $this->middleware('can:product_final.index')->only(['index', 'get_products_finals']);
        $this->middleware('can:product_final.store')->only('store');
        $this->middleware('can:product_final.show')->only('show');
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
                ["field" => "products_finals.id"],
                ["field" => "name", "conditions" => "CONCAT(products_finals.name, ' ', products_finals.presentation)"],
                ["field" => "stock", "conditions" => "CONCAT(FORMAT(products_finals.stock, 2), ' KG')"],
                ["field" => "type", "conditions" => "products_finals.type"],
                ["field" => "products_finals.created_at"],
                ["field" => "products_finals.updated_at"]
            ];
            
            # Obteniendo la lista
            $primaries_products = GridboxNew::pagination("products_finals", $params, false, $request);
            return response()->json($primaries_products);
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_products_finals(Request $request)
    {
        try {
            $params = $request->all();

            #establezco los campos a mostrar
            $params["select"] = [
                ["field" => "products_finals.id"],
                ["field" => "name", "conditions" => "CONCAT(products_finals.name, ' ', products_finals.presentation, ' - ', products_finals.type )"],
            ];
            
            # Obteniendo la lista
            $primaries_products = GridboxNew::pagination("products_finals", $params, false, $request);
            return response()->json($primaries_products);
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

            if( empty( $request->name) ) throw new \Exception("Debes ingresar el nombre del producto", 1);
            if( empty( $request->type) ) throw new \Exception("Debes ingresar el tipo de producto", 1);
            if( empty( $request->name) ) throw new \Exception("Debes ingresar la presentacion del producto", 1);
            if( $request->stock < 0  || !is_numeric($request->stock) ) throw new \Exception("Debes ingresar una existencia correcta", 1);


            $new_product = new ProductsFinal([
                'name' => $request->name,
                'stock' => $request->stock,
                'type' => $request->type,
                'presentation' => $request->presentation,
            ]);
            $new_product->save();

            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => true,
                'quantity_after' => $new_product->stock,
                'quantity_before' => 0,
                'quantity' => $new_product->stock,
                'module' => 'Productos Finales',
                'observation' => 'Se creó ' . $new_product->name
            ]);
            $transaction->save();

            \DB::commit();
            return response()->json('Agregado Correctamente', 201);

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
            $product_final = ProductsFinal::findOrFail($id);
            return response()->json($product_final);

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
