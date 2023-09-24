<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;

class TransactionsController extends Controller
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

            # establezco los campos a mostrar
            $params["select"] = [
                ["field" => "transactions.id"],
                ["field" => "user", "conditions" => "CONCAT(users.name, ' ', users.lastname)"],
                ["field" => "action", "conditions" => "IF(transactions.action, '+', '-')"],
                ["field" => "quantity_after", "conditions" => "CONCAT(FORMAT(transactions.quantity_after, 2), ' KG')"],
                ["field" => "quantity_before", "conditions" => "CONCAT(FORMAT(transactions.quantity_before, 2), ' KG')"],
                ["field" => "quantity", "conditions" => "CONCAT(FORMAT(transactions.quantity, 2), ' KG')"],
                ["field" => "module", "conditions" => "transactions.module"],
                ["field" => "observation", "conditions" => "transactions.observation"],
                ["field" => "transactions.created_at"],
                ["field" => "transactions.updated_at"]
            ];

            # establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["users", "users.id", "=", "transactions.user_id"] ],
            ];

            # Obteniendo la lista
            $transactions = GridboxNew::pagination("transactions", $params, false, $request);
            return response()->json($transactions);
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
