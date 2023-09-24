<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CleaningTool;
use App\Models\GridboxNew;

class CleaningToolsController extends Controller
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
                ["field" => "cleaning_tools.id"],
                ["field" => "name", "conditions" => "cleaning_tools.name"],
                ["field" => "stock", "conditions" => "cleaning_tools.stock"],
                ["field" => "description", "conditions" => "cleaning_tools.description"],
                ["field" => "requirement", "conditions" => "cleaning_tools.requirement"],
                ["field" => "cleaning_tools.created_at"],
                ["field" => "cleaning_tools.updated_at"]
            ];

            # Obteniendo la lista
            $cleaning_tools = GridboxNew::pagination("cleaning_tools", $params, $request);
            return response()->json($cleaning_tools);

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

            if( empty( $request->name) )
                throw new \Exception("Debes ingresar el nombre del utensilio", 1);

            if( empty( $request->stock) )
                throw new \Exception("Debes ingresar la existencia del utensilio", 1);
            
            if( $request->stock < 0 )
                throw new \Exception("La existencia del utensilio no es correcta", 1);

            
            $new_clean_tool = new CleaningTool();
            $new_clean_tool->name = $request->name;
            $new_clean_tool->stock = $request->stock;
            $new_clean_tool->description = $request->description;
            $new_clean_tool->requirement = $request->requirement;

            $new_clean_tool->save();

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
    public function show($id)
    {
        try {
            $clean_tool = CleaningTool::findOrFail($id);

            return response()->json($clean_tool);

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
            
            if( empty( $request->name) )
                throw new \Exception("Debes ingresar el nombre del utensilio", 1);

            if( empty( $request->stock) )
                throw new \Exception("Debes ingresar la existencia del utensilio", 1);
            
            if( $request->stock < 0 )
                throw new \Exception("La existencia del utensilio no es correcta", 1);


            $clean_tool = CleaningTool::findOrFail($id);

            $clean_tool->name = $request->name;
            $clean_tool->stock = $request->stock;
            $clean_tool->description = $request->description;
            $clean_tool->requirement = $request->requirement;

            $clean_tool->save();

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
            $clean_tool = CleaningTool::findOrFail($id);

            $clean_tool->delete();

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
