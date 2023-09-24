<?php

namespace App\Models;

# clase que se encarga del listado y filtraciones
class Gridbox {

	public static function pagination($table = "", $params = [], $for_company = false, $request = null){
		$limit = empty($params["limit"]) ? 20 : ($params["limit"] > 20 ? 20 : $params["limit"]); 
		$page = empty( $params["page"] ) ? 1 : ( $params["page"] > 0 ? $params["page"] : 1 );
		$offset =  ($limit * ($page - 1) );
		$order_by = empty( $params["order_by"] ) ? [] : $params["order_by"];
		$filters = empty( $params["filters"] ) ? [] : $params["filters"];
		$selects = "";


		/*
		
		# determinando que un dato es por una compañia 
		# determinada a la que hay que buscar
		if( $for_company === true ){
			$user = $request->user();
			$aux = $request->all();
			if( empty($aux["company_id"]) ){
				$users_company = \App\UsersCompany::where(["user_id" => $user->id])->first();
				$aux["company_id"] = $users_company->company_id;
			}

			if( !empty($params["where"]) )
				$params["where"][] = ["{$table}.company_id", "=", $aux["company_id"]];
			else
				$params["where"] = array(["{$table}.company_id", "=", $aux["company_id"]]);
		}
		
		*/



		# permite hacer un registros 
		# de los queries realizados
		\DB::enableQueryLog();

		# estableciendo la tabla principal a consultar
		$db = \DB::table($table);

		# agregando los joins de las tablas determinandas
		if( !empty($params["join"]) ){
			foreach ($params["join"] as $join){
				switch ($join["type"]) {
					case "right": 
						$db->rightJoin( $join["join"][0], $join["join"][1], $join["join"][2], $join["join"][3]);
					break;
					case "left":
						$db->leftJoin( $db->raw($join["join"][0]), $db->raw($join["join"][1]), $db->raw($join["join"][2]), $db->raw($join["join"][3]) ); 
					break;
					default: 
						$db->join($join["join"][0], $join["join"][1], $join["join"][2], $join["join"][3]); 
					break;
				}
			}
		}

		# ordenando por el autocompletado
		if( !empty($params["autocomplete_id"]) ){
			$selects = "({$table}.id = {$params["autocomplete_id"]}) AS autocomplete_id";
			$db->orderBy("autocomplete_id", "desc");
		}

		# agregando where ha la table
		if( !empty($params["where"]) )
			$db->where($params["where"]);
		
		# agregando OR a los where
		if( !empty($params['orwhere']) )
			$db->orwhere($params['orwhere']);

		# agregando IN a los where
		if( !empty($params['wherein']) )
			$db->wherein($params['wherein']);

		# verificando los select
		if( !empty($params["select"])  ){
			foreach ($params["select"] as $column) {

				if( !empty($filters[ $column["field"]  ]) )
					$db->having($column["field"], "LIKE",  "%{$filters[$column["field"]]}%");
				
				if( !empty($order_by[ $column["field"] ]) )
					$db->orderBy($column["field"], $order_by[ $column["field"] ]);
				

				$selects .= !empty($selects) ? "," : ""; 
				$selects .= ( empty($column["conditions"]) ? "{$column['field']}" :  "{$column['conditions']} AS {$column['field']}");
			}
		}
		
		//return $db->select( $db->raw($selects) )->offset($offset)->limit($limit)->get();
		return $db->select( $db->raw($selects) )->get();
	}
}