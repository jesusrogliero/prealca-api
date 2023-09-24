<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gridbox;

class PrimariesProductsHistories extends Controller
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
                ["field" => "primaries_products_histories.id"],
                ["field" => "quantity", "conditions" => "primaries_products_histories.quantity"],
                ["field" => "primary_product", "conditions" => "primaries_products.name"],
                ["field" => "type_accion", "conditions" => "primaries_products_histories.type_accion"],
                ["field" => "primaries_products_histories.created_at"],
                ["field" => "primaries_products_histories.updated_at"]
            ];
            

            #establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["primaries_products", "primaries_products.id", "=", "primaries_products_histories.primary_product_id"] ]
            ];

            # Obteniendo la lista
            $primaries_products = Gridbox::pagination("primaries_products_histories", $params, false, $request);
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

}
