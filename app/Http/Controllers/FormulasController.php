<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\Line;
use App\Models\Formula;
use App\Models\FormulasItem;

class FormulasController extends Controller
{

    public function __construct() {
        $this->middleware('can:formula.index')->only('index');
        $this->middleware('can:formula.store')->only('store');
        $this->middleware('can:formula.show')->only('show');
        $this->middleware('can:formula.update')->only('update');
        $this->middleware('can:formula.destroy')->only('destroy');
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
                ["field" => "formulas.id"],
                ["field" => "name", "conditions" => "CONCAT(formulas.name, ' ' ,FORMAT(formulas.quantity_batch, 2) ,'Kg')"],
                ["field" => "line", "conditions" => "lines.name"],
                ["field" => "quantity_batch", "conditions" => "CONCAT(FORMAT(formulas.quantity_batch, 2), ' Kg')"],
                ["field" => "total_formula", "conditions" => "CONCAT(FORMAT(formulas.total_formula, 2), ' Kg')"],
                ["field" => "formulas.created_at"],
                ["field" => "formulas.updated_at"]
            ];

           #establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["lines", "lines.id", "=", "formulas.line_id"] ],
            ];
            
            # Obteniendo la lista
            $purchases_orders = GridboxNew::pagination("formulas", $params, false, $request);
            return response()->json($purchases_orders);
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
            \DB::beginTransaction();

            if(empty($request->name)) throw new \Exception("El nombre de la formula es requerido", 1);
            if(empty($request->line_id)) throw new \Exception("La linea de la formula es requerida", 1);
            if(empty($request->quantity_batch)) throw new \Exception("La cantidad por batch es requerida", 1);
            if($request->quantity_batch <= 0) throw new \Exception("La cantidad por batch no es correcta", 1);

            $new_formula = new Formula();
            $new_formula->name = $request->name;
            $new_formula->line_id = $request->line_id;
            $new_formula->quantity_batch = $request->quantity_batch;
            $new_formula->save();

            $user = $request->user();
            \DB::table('transactions')->insert([
                'user_id' => $user->id,
                'action' => true,
                'module' => 'Formula',
                'observation' => 'Se registro: '. $new_formula->name,
                'created_at' => new \DateTime(),
            ]);

            \DB::commit();
            return response()->json('Formula Creada Correctamente', 201);

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
        try{   
            $formula = Formula::findOrFail($id);
            return response()->json($formula);

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
        try{      
            \DB::beginTransaction();

            $formula = Formula::findOrFail($id);

            $formula->name = $request->name;
            $formula->line_id = $request->line_id;
            $formula->quantity_batch = $request->quantity_batch;
            $formula->save();
            
            $user = $request->user();
            \DB::table('transactions')->insert([
                'user_id' => $user->id,
                'action' => true,
                'module' => 'Formula',
                'observation' => 'Se actualizó: '. $formula->name,
                'created_at' => new \DateTime(),
            ]);

            \BD::commit();
            return response()->json('Formula Actualizada', 202);

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
    public function destroy(Request $request ,$id)
    {
        try{    
            \DB::beginTransaction();

            $formula = Formula::findOrFail($id);
            $items = FormulasItem::where('formula_id', '=', $formula->id)->delete();
            $formula->delete();  
            
            $user = $request->user();
            \DB::table('transactions')->insert([
                'user_id' => $user->id,
                'action' => false,
                'module' => 'Formula',
                'observation' => 'Eliminó: '. $formula->name,
                'created_at' => new \DateTime(),
            ]);

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
