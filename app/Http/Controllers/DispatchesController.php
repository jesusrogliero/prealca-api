<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\Dispatch;
use App\Models\DispatchesItem;
use App\Models\ProductsFinal;
use App\Models\Transaction;

class DispatchesController extends Controller
{

    public function __construct() {
        $this->middleware('can:dispatch.index')->only('index');
        $this->middleware('can:dispatch.store')->only('store');
        $this->middleware('can:dispatch.show')->only('show');
        $this->middleware('can:dispatch.update')->only('update');
        $this->middleware('can:dispatch.destroy')->only('destroy');
        $this->middleware('can:dispatch.approve')->only('approve');
        $this->middleware('can:dispatch.get_details')->only('get_details');
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
                ["field" => "dispatches.id"],
                ["field" => "sica_code", "conditions" => "dispatches.sica_code"],
                ["field" => "state", "conditions" => "dispatches_states.name"],
                ["field" => "receiver_name", "conditions" => "receivers.name"],
                ["field" => "guide_sada", "conditions" => "dispatches.guide_sada"],
                ["field" => "total", "conditions" => "CONCAT(dispatches.total, ' KG')"],
                ["field" => "drive_name", "conditions" => "dispatches.drive_name"],
                ["field" => "drive_identity", "conditions" => "CONCAT('V-', FORMAT(dispatches.drive_identity,0))"],
                ["field" => "dispatches.created_at"],
                ["field" => "dispatches.updated_at"]
            ];

           #establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["dispatches_states", "dispatches_states.id", "=", "dispatches.state_id"] ],
                [ "type" => "inner", "join" => ["receivers", "receivers.id", "=", "dispatches.receiver_id"] ],
            ];
            
            # Obteniendo la lista
            $dispatches = GridboxNew::pagination("dispatches", $params, false, $request);
            return response()->json($dispatches);
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
        try{  

            if(empty($request->receiver_id)) throw new \Exception('Debe seleccionar el receptor');
            if(empty($request->sica_code)) throw new \Exception('Debe ingresar el Codigo Sica');
            if(empty($request->guide_sada)) throw new \Exception('Debe ingresar el Nro de la Guia Sada');
            if(empty($request->drive_name)) throw new \Exception('Debe ingresar el nombre del chofer');
            if(empty($request->drive_identity)) throw new \Exception('Debe ingresar la identificación del chofer');
            
            // Nuevo despacho
            $new_dispatch = new Dispatch();
            $new_dispatch->receiver_id = $request->receiver_id;
            $new_dispatch->state_id = 1;
            $new_dispatch->sica_code = $request->sica_code;
            $new_dispatch->guide_sada = $request->guide_sada;
            $new_dispatch->observation = $request->observation;
            $new_dispatch->drive_name = $request->drive_name;
            $new_dispatch->drive_identity = $request->drive_identity;
            $new_dispatch->save();

            return response()->json('Despacho Creado Correctamente', 201);

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
            $dispatch = Dispatch::findOrFail($id);
            return response()->json($dispatch);
            
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
     * set observation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function set_observation(Request $request, $id)
    {
        try{      

            $dispatch = Dispatch::findOrFail($id);

            if( $dispatch->state_id != 1)
                throw new \Exception("No Es Posible Editar Un Despacho Procesado", 1);

            $dispatch->observation = $request->observation;
            $dispatch->save();
            
            return response()->json('Guardado', 202);

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

    public function get_details(Request $request, $id) {
        try{
            
            $order = Dispatch::findOrFail($id);


            $receiver = \DB::table('receivers')
            ->select('receivers.*')
            ->selectRaw("CONCAT(types_identities.type, '-', receivers.identity) as identityF")
            ->join('types_identities', 'types_identities.id', '=' , 'receivers.type_identity_id')
            ->where('receivers.id', '=', $order->receiver_id)
            ->get();
            
            $items = \DB::table('dispatches_items')
            ->select('products_finals.name as product_final')
            ->selectRaw('CONCAT( FORMAT(dispatches_items.quantity, 2), " Kg") as quantity')
            ->join('products_finals', 'products_finals.id', '=' , 'dispatches_items.product_final_id')
            ->where('dispatches_items.dispatch_id', '=', $order->id)
            ->get();

            return response()->json([
                'dispatch' => $order,
                'dispatch_items' => $items,
                'receiver' => $receiver[0]
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, $id)
    {
        try{      
            \DB::beginTransaction();

            $dispatch = Dispatch::findOrFail($id);
            $dispatch_items = DispatchesItem::where('dispatch_id', $dispatch->id)->get();

            if( $dispatch->state_id != 1)
                throw new \Exception("No Es Posible aprobar Un Despacho Procesado", 1);

            foreach ($dispatch_items as $item) {
                $product_final = ProductsFinal::findOrFail($item->product_final_id);

                if($product_final->stock - $item->quantity < 0)
                    throw new \Exception("No hay suficiente stock de" . $product_final->name, 1);
                
                $transaction = new Transaction([
                    'user_id' => $request->user()->id,
                    'action' => false,
                    'quantity_after' => $product_final->stock - $item->quantity,
                    'quantity_before' => $product_final->stock,
                    'quantity' => $item->quantity,
                    'module' => 'Productos Primarios',
                    'observation' => 'ingreso al inventario de ' . $product_final->name . ' ' . $product_final->presentation
                ]);
                $transaction->save();

                $product_final->stock = $product_final->stock - $item->quantity;
                $product_final->save();

            }
          
            $dispatch->state_id = 2;
            $dispatch->save();
            
            \DB::commit();
            return response()->json('Despacho Aprobado', 202);

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
        try{      
            if(empty($request->receiver_id)) throw new \Exception('Debe seleccionar el receptor');
            if(empty($request->sica_code)) throw new \Exception('Debe ingresar el Codigo Sica');
            if(empty($request->guide_sada)) throw new \Exception('Debe ingresar el Nro de la Guia Sada');
            if(empty($request->drive_name)) throw new \Exception('Debe ingresar el nombre del chofer');
            if(empty($request->drive_identity)) throw new \Exception('Debe ingresar la identificación del chofer');

            $dispatch = Dispatch::findOrFail($id);

            if( $dispatch->state_id != 1)
                throw new \Exception("No Es Posible Editar Un Despacho Procesado", 1);

            $dispatch->receiver_id = $request->receiver_id;
            $dispatch->sica_code = $request->sica_code;
            $dispatch->guide_sada = $request->guide_sada;
            $dispatch->observation = $request->observation;
            $dispatch->drive_name = $request->drive_name;
            $dispatch->drive_identity = $request->drive_identity;
            $dispatch->save();
            
            return response()->json('Despacho Actualizado', 202);

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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{  
            $dispatch = Dispatch::findOrFail($id);
            
            if($dispatch->state_id != 1)
                throw new \Exception("Este despacho ya fue procesado", 1);
            
            DispatchsItems::where('dispatch_id','=', $dispatch->id)->delete();
            $dispatch->delete();

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
