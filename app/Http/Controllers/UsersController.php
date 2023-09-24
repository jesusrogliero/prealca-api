<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GridBox;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
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
                ["field" => "users.id"],
                ["field" => "name", "conditions" => "users.name"],
                ["field" => "lastname", "conditions" => "users.lastname"],
                ["field" => "email", "conditions" => "users.email"],
                ["field" => "users.created_at"],
                ["field" => "users.updated_at"]
            ];
          
            # Obteniendo la lista
            $users = Gridbox::pagination("users", $params, false, $request);
            return response()->json($users);
        
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

            if( empty( $request->name ) )
                throw new \Exception("Debes ingresar el nombre del usuario", 1);

            if( empty( $request->lastname ) )
                throw new \Exception("Debes ingresar el apellido del usuario", 1);
            
            if( empty( $request->email ) )
                throw new \Exception("Debes ingresar el correo electronico del usuario", 1);
            
            if( empty( $request->password ) )
                throw new \Exception("Debes ingresar una contraseña provicional", 1);
            
            $user = \DB::table('users')->where('email', '=', $request->email)->first();
            
            if( !empty( $user ) )
                throw new \Exception("La direccion de correo ya esta en uso", 1);

            if( $request->password != $request->password_confirm)
                throw new \Exception("Las contraseñas no coinciden", 1);
                
            $new_user = new User();
            $new_user->name = $request->name;
            $new_user->lastname = $request->lastname;
            $new_user->email = $request->email;
            $new_user->password = Hash::make($request->password);
            $new_user->save();


            \DB::commit();
            return response()->json("Usuario Creado Correctamente", 201);
        
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

            $user = User::findOrFail($id);
            return response()->json($user);
        
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
            
            if( empty( $request->name ) )
                throw new \Exception("Debes ingresar el nombre del usuario", 1);

            if( empty( $request->lastname ) )
                throw new \Exception("Debes ingresar el apellido del usuario", 1);
            
            if( empty( $request->email ) )
                throw new \Exception("Debes ingresar el correo electronico del usuario", 1);
                
            $user = User::findOrFail($id);

            if($user->email != $request->email) {
                $user2 = \DB::table('users')->where('email', '=', $request->email)->first();
                if( !empty( $user2 ) )
                    throw new \Exception("La direccion de correo ya esta en uso", 1);
            }

            $user->email = $request->email;
            $user->name = $request->name;
            $user->lastname = $request->lastname;            
            $user->save();

            \DB::commit();
            return response()->json("Actualizado Correctamente", 202);
        
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
    public function destroy($id)
    {
        try {  
            \DB::beginTransaction();


            $user = User::findOrFail($id);
            $user->delete();

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
