<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridboxNew;
use App\Models\Formula;
use App\Models\FormulasItem;

class FormulasItemsController extends Controller
{

    public function __construct() {
        $this->middleware('can:formula_item.index')->only('get_items');
        $this->middleware('can:formula_item.store')->only('store');
        $this->middleware('can:formula_item.show')->only('show');
        $this->middleware('can:formula_item.update')->only('update');
        $this->middleware('can:formula_item.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_items(Request $request, $formula_id)
    {
        try {
            $params = $request->all();
            
            # establezco los campos a mostrar
            $params["select"] = [
                ["field" => "formulas_items.id"],
                ["field" => "primary_product", "conditions" => "primaries_products.name"],
                ["field" => "quantity", "conditions" => "CONCAT( FORMAT(formulas_items.quantity, 2), ' Kg')"],
                ["field" => "formulas_items.created_at"],
                ["field" => "formulas_items.updated_at"]
            ];

           # establezco los joins necesarios
           $params["join"] = [
                [ "type" => "inner", "join" => ["primaries_products", "primaries_products.id", "=", "formulas_items.primary_product_id"] ],
            ];

            $params['where'] = [['formulas_items.formula_id', '=', $formula_id]];
            
            # Obteniendo la lista
            $formula_items = GridboxNew::pagination("formulas_items", $params, false, $request);
            return response()->json($formula_items);

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

            if(empty($request->primary_product_id)) throw new \Exception('El producto primario es requerido', 1);
            if(empty($request->quantity)) throw new \Exception('LA cantidad de la formula es requerida');

            $item = new FormulasItem();
            $item->primary_product_id = $request->primary_product_id;
            $item->quantity = $request->quantity;
            $item->formula_id = $request->formula_id;
            $item->save();

            $formula = Formula::findOrFail($request->formula_id);
            $formula->total_formula = $formula->total_formula + $item->quantity;
            $formula->save();

            $user = $request->user();
            \DB::table('transactions')->insert([
                'user_id' => $user->id,
                'action' => true,
                'module' => 'Formula Ingrediente',
                'observation' => ' Nuevo ingrediente en: '. $formula->name,
                'created_at' => new \DateTime(),
            ]);

            \DB::commit();
            return response()->json('Ingrediente Agregado Correctamente', 201);

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

            $item = FormulasItem::findOrFail($id);
            return response()->json($item);

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


            if(empty($request->primary_product_id)) throw new \Exception('El producto primario es requerido', 1);
            if(empty($request->quantity)) throw new \Exception('LA cantidad de la formula es requerida');
            
            $item = FormulasItem::findOrFail($id);

            $formula = Formula::findOrFail($item->formula_id);
            $formula->total_formula = $formula->total_formula - $item->quantity;

            
            $item->primary_product_id = $request->primary_product_id;
            $item->quantity = $request->quantity;
            $item->save();

            $formula->total_formula = $formula->total_formula + $item->quantity;
            $formula->save();

            \DB::commit();
            return response()->json('Ingrediente Actualizado Correctamente', 202);

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

            $item = FormulasItem::findOrFail($id);
            $formula = Formula::findOrFail($item->formula_id);
            $formula->total_formula = $formula->total_formula - $item->quantity;

            $formula->save();
            $item->delete();


            $user = $request->user();
            \DB::table('transactions')->insert([
                'user_id' => $user->id,
                'action' => false,
                'module' => 'Formula Ingrediente',
                'observation' => 'Se eliminó un ingrediente de: '. $formula->name,
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
