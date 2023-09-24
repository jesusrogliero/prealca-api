<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;

class TypesIdentitiesController extends Controller
{
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
                ["field" => "types_identities.id"],
                ["field" => "type", "conditions" => "types_identities.type"],
                ["field" => "name", "conditions" => "types_identities.name"],
                ["field" => "types_identities.created_at"],
                ["field" => "types_identities.updated_at"]
            ];
            
            # Obteniendo la lista
            $types_identities = GridboxNew::pagination("types_identities", $params, false, $request);
            return response()->json($types_identities);
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
