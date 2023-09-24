<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\Provider;
use App\Models\User;

class ProvidersController extends Controller
{

    public function __construct() {
        $this->middleware('can:providers.index')->only('index');
        $this->middleware('can:providers.store')->only('store');
        $this->middleware('can:providers.show')->only('show');
        $this->middleware('can:providers.update')->only('update');
        $this->middleware('can:providers.destroy')->only('destroy');
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
                ["field" => "providers.id"],
                ["field" => "name", "conditions" => "providers.name"],
                ["field" => "identity", "conditions" => "CONCAT(types_identities.type, '-' , FORMAT(providers.identity, 0))"],
                ["field" => "address", "conditions" => "providers.address"],
                ["field" => "phone", "conditions" => "providers.phone"],
                ["field" => "providers.created_at"],
                ["field" => "providers.updated_at"]
            ];

            #establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["types_identities", "types_identities.id", "=", "providers.type_identity_id"] ],
            ];
            
            # Obteniendo la lista
            $providers = GridboxNew::pagination("providers", $params, false, $request);
            return response()->json($providers);
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

            if( empty($request->name) )
                throw new \Exception("El nombne del provedor es obligatorio");

            if( empty($request->identity) )
                throw new \Exception("Debes ingresar la identificación del Provedor");
            
            if( empty($request->type_identity_id) )
                throw new \Exception("Debes ingresar el tipo de identificación del Provedor");
                
            if( empty($request->address) )
                throw new \Exception("Debe ingresar una observacion");
            
            if( empty($request->phone) )
                throw new \Exception("Debe ingresar el telefono del provedor");

            # Registro el provedor
            $new_provider = new Provider();
            $new_provider->name = $request->name;
            $new_provider->identity = $request->identity;
            $new_provider->type_identity_id = $request->type_identity_id;
            $new_provider->address =$request->address;
            $new_provider->phone = $request->phone;
            $new_provider->save();

           return response()->json('Registrado Correctamente', 201);

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
    public function show(Request $request, $id)
    {
        try {
            
            $provider = \DB::table('providers')
            ->join('types_identities', 'types_identities.id', '=', 'providers.type_identity_id')
            ->select('providers.*', 'types_identities.type')
            ->where('providers.id', '=', $id)
            ->get();

           return response()->json($provider[0]);

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
  
            $provider = Provider::findOrFail($id);
            $provider->name = $request->name;
            $provider->identity = $request->identity;
            $provider->type_identity_id = $request->type_identity_id;
            $provider->address =$request->address;
            $provider->phone = $request->phone;
            $provider->save();

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
    public function destroy(Request $request, $id)
    {
        try {
           
            $provider = Provider::findOrFail($id);
            $provider->delete();

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
