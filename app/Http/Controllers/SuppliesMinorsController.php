<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridBoxNew;
use App\Models\SuppliesMinor;
use App\Models\Transaction;

class SuppliesMinorsController extends Controller
{
    public function __construct() {
        $this->middleware('can:supplies_minors.index')->only('index');
        $this->middleware('can:supplies_minors.store')->only('store');
        $this->middleware('can:supplies_minors.show')->only('show');
        $this->middleware('can:supplies_minors.update')->only('update');
        $this->middleware('can:supplies_minors.update')->only('update');
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
                ["field" => "supplies_minors.id"],
                ["field" => "name", "conditions" => "supplies_minors.name"],
                ["field" => "stock", "conditions" => "CONCAT(FORMAT(supplies_minors.stock, 2), ' KG')"],
                ["field" => "supplies_minors.created_at"],
                ["field" => "supplies_minors.updated_at"]
            ];

            # Obteniendo la lista
            $supplies_minors = GridboxNew::pagination("supplies_minors", $params, false, $request);
            return response()->json($supplies_minors);
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

            if(empty($request->name)) throw new \Exception("El nombre del empaque es requerido", 1);
            if($request->stock < 0) throw new \Exception("La cantidad del Articulo no es correcta", 1);
            if($request->consumption_weight_package < 0) throw new \Exception("el peso por empaque no es correcto", 1);
            
            # Creo el articulo de la orden
            $new_product = new SuppliesMinor();
            $new_product->name = $request->name;
            $new_product->stock = $request->stock;
            $new_product->consumption_weight_package = $request->consumption_weight_package;
            $new_product->save();

            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => true,
                'quantity_after' => $new_product->stock,
                'quantity_before' => 0,
                'quantity' => $new_product->stock,
                'module' => 'Insumos Menores',
                'observation' => 'Se creó ' . $new_product->name
            ]);
            $transaction->save();

            \DB::commit();
            return response()->json('Guardado Correctamente', 201);

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
            $pnc = SuppliesMinor::findOrFail($id);
            return response()->json($pnc);

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

            if( empty($request->name) ) throw new \Exception("El nombre del producto es obligatorio");
            if( $request->stock < 0 ) throw new \Exception("La existencia no puede ser menor a cero");
            if($request->consumption_weight_package < 0) throw new \Exception("el peso por empaque no es correcto", 1);

            $product = SuppliesMinor::findOrFail($id);

            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => $product->stock > $request->stock ? false : true,
                'quantity_after' => $request->stock,
                'quantity_before' => $product->stock,
                'quantity' => $product->stock - $request->stock,
                'module' => 'Insumos Menores',
                'observation' => 'Se actualizó ' . $product->name
            ]);
            $transaction->save();

            $product->name = $request->name;
            $product->stock = $request->stock;
            $product->consumption_weight_package = $request->consumption_weight_package;
            $product->save();

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            \DB::beginTransaction();

            $product = SuppliesMinor::findOrFail($id);

            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => false,
                'quantity_after' => 0,
                'quantity_before' => $product->stock,
                'quantity' => $product->stock,
                'module' => 'Insumos Menores',
                'observation' => 'Se eliminó '. $product->name
            ]);
            $transaction->save();

            $product->delete();
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
