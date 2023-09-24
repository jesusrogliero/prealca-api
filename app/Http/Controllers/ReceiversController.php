<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receiver;
use App\Models\Transaction;
use App\Models\GridboxNew;

class ReceiversController extends Controller
{

    public function __construct() {
        $this->middleware('can:receivers.index')->only('index');
        $this->middleware('can:receivers.store')->only('store');
        $this->middleware('can:receivers.show')->only('show');
        $this->middleware('can:receivers.update')->only('update');
        $this->middleware('can:receivers.destroy')->only('destroy');
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
                ["field" => "receivers.id"],
                ["field" => "name", "conditions" => "receivers.name"],
                ["field" => "identity", "conditions" => "CONCAT(types_identities.type, '-', FORMAT(receivers.identity, 2))"],
                ["field" => "address", "conditions" => "receivers.address"],
                ["field" => "phone", "conditions" => "receivers.phone"],
                ["field" => "receivers.created_at"],
                ["field" => "receivers.updated_at"]
            ];

            #establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["types_identities", "types_identities.id", "=", "receivers.type_identity_id"] ],
            ];
            
            # Obteniendo la lista
            $receivers = GridboxNew::pagination("receivers", $params, false, $request);
            return response()->json($receivers);
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

            if( empty($request->name) ) throw new \Exception("El nombre del receptor es obligatorio");
            if( empty($request->type_identity_id) ) throw new \Exception("El tipo de identificacion es obligatorio");
            if( $request->identity < 0 ) throw new \Exception("El numero de identificación es obligatorio");

           $new_receiver = new Receiver();
           $new_receiver->name = $request->name;
           $new_receiver->type_identity_id = $request->type_identity_id;
           $new_receiver->identity = $request->identity;
           $new_receiver->address = $request->address;
           $new_receiver->phone = $request->phone;
           $new_receiver->save();

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
        $receiver = Receiver::findOrFail($id);
        return response()->json($receiver);

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

            if( empty($request->name) ) throw new \Exception("El nombre del producto es obligatorio");
            if ( empty( $request->identity) ) throw new \Exception("Debes ingresar la identidad del receptor");
            if( empty( $request->type_identity_id) ) throw new \Exception("Debes seleccion el tipo de identidad");

            $receiver = Receiver::findOrFail($id);
            $receiver->name = $request->name;
            $receiver->identity = $request->identity;
            $receiver->type_identity_id = $request->type_identity_id;
            $receiver->address = $request->address;
            $receiver->phone = $request->phone;
            $receiver->save();

            return response()->json('Actualizado Correctamente', 202);

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
        try {
            $receiver = Receiver::findOrFail($id);
            $receiver->delete();
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
