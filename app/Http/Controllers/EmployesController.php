<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employe;
use App\Models\Gridbox;
use App\Models\City;

class EmployesController extends Controller
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
                ["field" => "employes.id"],
                ["field" => "name", "conditions" => "employes.name"],
                ["field" => "lastname", "conditions" => "employes.lastname"],
                ["field" => "position", "conditions" => "positions.name"],
                ["field" => "cedula", "conditions" => "employes.cedula"],
                ["field" => "data_admission", "conditions" => "employes.data_admission"],
                ["field" => "address", "conditions" => "employes.address"],
                ["field" => "city", "conditions" => "cities.name"],
                ["field" => "province", "conditions" => "provinces.name"],
                ["field" => "nacionality", "conditions" => "employes.nacionality"],
                ["field" => "phone", "conditions" => "employes.phone"],
                ["field" => "genere", "conditions" => "IF(employes.genere = 1,'Hombre', 'Mujer')"],
                ["field" => "date_brith", "conditions" => "employes.date_brith"],
                ["field" => "employes.created_at"],
                ["field" => "employes.updated_at"]
            ];

            #establezco los joins necesarios
            $params["join"] = [
                [ "type" => "inner", "join" => ["positions", "positions.id", "=", "employes.position_id"] ],
                [ "type" => "inner", "join" => ["provinces", "provinces.id", "=", "employes.province_id"] ],
                [ "type" => "inner", "join" => ["cities", "cities.id", "=", "employes.city_id"] ],
            ];
                        
            # Obteniendo la lista
            $employes = Gridbox::pagination("employes", $params, false, $request);
            return response()->json($employes);
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


            if( empty($request->name) ) throw new \Exception("Debes ingresar los nombres del empleado", 1);
            if( empty($request->lastname) ) throw new \Exception("Debes ingresar los apellidos del empleado", 1);
            if( empty($request->position_id) ) throw new \Exception("Debes seleccionar la posicion del empleado", 1);
            if( empty($request->cedula) ) throw new \Exception("Debes ingresar la cedula del empleado", 1);
            if( empty($request->data_admission) ) throw new \Exception("Debes ingresar la fecha de admision del empleado", 1);
            if( empty($request->address) ) throw new \Exception("Debes ingresar la dirrecion del empleado", 1);
            if( empty($request->city_id) ) throw new \Exception("Debes ingresar la ciudad donde vive el empleado", 1);
            if( empty($request->province_id) ) throw new \Exception("Debes ingresar el estado donde vive el empleado", 1);
            if( empty($request->nacionality) ) throw new \Exception("Debes ingresar la nacionalidad del empleado", 1);
            if( empty($request->phone) ) throw new \Exception("Debes ingresar el numero de telefono del empleado", 1);
            if( empty($request->genere) ) throw new \Exception("Debes seleccionar el genero del empleado", 1);
            if( empty($request->date_brith) ) throw new \Exception("Debes ingresar la fecha de nacimiento del empleado", 1);

           $new_employe = new Employe();

           $new_employe->name = $request->name;
           $new_employe->lastname = $request->lastname;
           $new_employe->position_id = $request->position_id;
           $new_employe->cedula = $request->cedula;
           $new_employe->data_admission = $request->data_admission;
           $new_employe->address = $request->address;
           $new_employe->city_id = $request->city_id;
           $new_employe->province_id = $request->province_id;
           $new_employe->nacionality = $request->nacionality;
           $new_employe->phone = $request->phone;
           $new_employe->genere = $request->genere;
           $new_employe->date_brith = $request->date_brith;

           $new_employe->save();

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

            $employe = Employe::findOrFail($id);
            return response()->json($employe);

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
     * Display all resources.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_provinces(Request $request)
    {
        try {

            $provinces = \DB::table('provinces')->select(['id', 'name'])->get();
            return response()->json($provinces);

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
     * Display all resources.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_cities_of_provinces(Request $request, $province_id)
    {
        try {

            if( empty( $request->province_id) )
                return response()->json(null);
                
            $cities = City::where('province_id', '=', $request->province_id)->get(['id','name']);
            return response()->json($cities);

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
    public function show_positions(Request $request)
    {
        try {

            $positions = \DB::table('positions')->get();
            return response()->json($positions);

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

            if( empty($request->name) ) throw new \Exception("Debes ingresar los nombres del empleado", 1);
            if( empty($request->lastname) ) throw new \Exception("Debes ingresar los apellidos del empleado", 1);
            if( empty($request->position_id) ) throw new \Exception("Debes seleccionar la posicion del empleado", 1);
            if( empty($request->cedula) ) throw new \Exception("Debes ingresar la cedula del empleado", 1);
            if( empty($request->data_admission) ) throw new \Exception("Debes ingresar la fecha de admision del empleado", 1);
            if( empty($request->address) ) throw new \Exception("Debes ingresar la dirrecion del empleado", 1);
            if( empty($request->city_id) ) throw new \Exception("Debes ingresar la ciudad donde vive el empleado", 1);
            if( empty($request->province_id) ) throw new \Exception("Debes ingresar el estado donde vive el empleado", 1);
            if( empty($request->nacionality) ) throw new \Exception("Debes ingresar la nacionalidad del empleado", 1);
            if( empty($request->phone) ) throw new \Exception("Debes ingresar el numero de telefono del empleado", 1);
            if( empty($request->genere) ) throw new \Exception("Debes seleccionar el genero del empleado", 1);
            if( empty($request->date_brith) ) throw new \Exception("Debes ingresar la fecha de nacimiento del empleado", 1);


            $employe = Employe::findOrFail($id);
            
            # actualizo los datos

            $employe->name = $request->name;
            $employe->lastname = $request->lastname;
            $employe->position_id = $request->position_id;
            $employe->cedula = $request->cedula;
            $employe->data_admission = $request->data_admission;
            $employe->address = $request->address;
            $employe->city_id = $request->city_id;
            $employe->province_id = $request->province_id;
            $employe->nacionality = $request->nacionality;
            $employe->phone = $request->phone;
            $employe->genere = $request->genere;
            $employe->date_brith = $request->date_brith;

            $employe->save();

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

            $employe = Employe::findOrFail($id);
            $employe->delete();
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
