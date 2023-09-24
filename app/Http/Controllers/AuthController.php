<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AuthController extends Controller
{


    # funcion que se encarga de registrar al usuario
    public function signup(Request $request) {

        try {
            \DB::beginTransaction();
            # Aplico la validacion
            $request->validate([
                'name' => 'required',
                'lastname' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
            ]);
                

            # Creo un Nuevo Usuario
            $user = new User();
            $user->name = $request->name;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            # creamos el token de autenticacion
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->save();

            \DB::commit();

            # retorno el AccessToken y el codigo 201
            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'expires_at'   => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                'user' => $user
            ], 201);

        } catch(\Exception $e) {
            \DB::rollback();
            \Log::info("Error  ({$e->getCode()}):  {$e->getMessage()}  in {$e->getFile()} line {$e->getLine()}");  
            return \Response::json([
                'line' => $e->getLine(),
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], 422);
        }
    }


    # iniciar sesión
    public function login (Request $request) {

    	try {

	    	# Aplicando la validacion
	    	$request->validate([
	    		'email' => 'required|string',
	    		'password' => 'required|string'
	    	]);

	    	# Verifico las credenciales
	    	$credenciales = request(['email', 'password']);
	    	
            # no ha sido autorizado el usuario
            if(!Auth::attempt($credenciales)) 
                throw new \Exception("Usted no esta autorizado", 1);
                

	    	# Creamos el token de autenticacion
	    	$user = $request->user();
	    	$tokenResult = $user->createToken('Personal Access Token');
	    	$token = $tokenResult->token;
            $token->save();


            # retorna 202 que es !Ha sido completada la accion¡
	    	return response()->json([
                'token' => $tokenResult->accessToken,
                'expires_at'   => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                'user' => $user,
                'role' => $user->getRoleNames()[0]
	        ], 202);

    	}catch(\Exception $e) {
    		\Log::info("Error  ({$e->getCode()}):  {$e->getMessage()}  in {$e->getFile()} line {$e->getLine()}");
    		
    		return \Response::json([
    			'line' => $e->getLine(),
    			'message' => $e->getMessage(),
    			'code' => $e->getCode()
    		], 422);
    	}
    }

    public function getSession(Request $request) {
        return response()->json( ['valid' => auth()->check()], 200);
    }

    public function getUser(Request $request) {
        $user = $request->user();
        $role = $user->getRoleNames();
        return response()->json(['user' => $user, 'role' => $role[0]], 200);
    }

}
