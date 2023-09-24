<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\SuppliesMinorsNoconform;
use App\Models\SuppliesMinor;
use App\Models\Transaction;

class SuppliesMinorsNoconformController extends Controller
{
    public function __construct() {
        $this->middleware('can:supplies_minors_noconform.index')->only('index');
        $this->middleware('can:supplies_minors_noconform.store')->only('store');
        $this->middleware('can:supplies_minors_noconform.show')->only('show');
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
                ["field" => "supplies_minors_noconforms.id"],
                ["field" => "supplie_minor", "conditions" => "supplies_minors.name"],
                ["field" => "quantity", "conditions" => "CONCAT(FORMAT(supplies_minors_noconforms.quantity, 2), ' KG')"],
                ["field" => "observation", "conditions" => "supplies_minors_noconforms.observation"],
                ["field" => "supplies_minors_noconforms.created_at"],
                ["field" => "supplies_minors_noconforms.updated_at"]
            ];

            
            #establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["supplies_minors", "supplies_minors.id", "=", "supplies_minors_noconforms.supplie_minor_id"] ]
            ];
            
            # Obteniendo la lista
            $supplies_minors_noconforms = GridboxNew::pagination("supplies_minors_noconforms", $params, false, $request);
            return response()->json($supplies_minors_noconforms);
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

            if(empty($request->supplie_minor_id)) throw new \Exception("Debes seleccionar el insumo antes de continuar", 1);
            if($request->stock < 0) throw new \Exception("La cantidad del insumo no es correcta", 1);
            
            # Creo el articulo de la orden
            $new_product = new SuppliesMinorsNoconform();
            $new_product->supplie_minor_id = $request->supplie_minor_id;
            $new_product->quantity = $request->quantity;
            $new_product->save();


            $transaction = new Transaction([
                'user_id' => $request->user()->id,
                'action' => true,
                'quantity_after' => $new_product->quantity,
                'quantity_before' => 0,
                'quantity' => $new_product->quantity,
                'module' => 'Insumos Menores',
                'observation' => 'Se creó ' . SuppliesMinor::findOrFail($new_product->supplie_minor_id)->name
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
            $pnc = SuppliesMinorsNoconform::findOrFail($id);
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

}
