<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatisticsController extends Controller
{

    public function get_statistics(Request $request)
    {
        try{  

            $interval = null;
            if( !empty($request->interval) && !empty($request->interval_period)) {
               $interval =  'NOW() - INTERVAL ' . $request->interval . ' ' . $request->interval_period;
            }     

            $purchases_statistics = 'select CONCAT(FORMAT(SUM(purchases_orders.total_load), 2), " Kg") as quantity from purchases_orders WHERE purchases_orders.state_id = 2';
            $consumptions_statistics = 'select CONCAT(FORMAT(SUM(productions_consumptions.consumption_production), 2), " Kg") as quantity from productions_consumptions INNER JOIN productions_orders on productions_orders.id = productions_consumptions.production_order_id WHERE productions_orders.state_id = 2';
            $finals_to_warehouse_statistics = 'select CONCAT(FORMAT(SUM(products_finals_to_warehouses.quantity), 2), " Kg") as quantity from products_finals_to_warehouses WHERE products_finals_to_warehouses.state_id = 2';

            if( !empty($interval) ) {
                $purchases_statistics .= " and purchases_orders.created_at >= " . $interval;
                $consumptions_statistics .= " and productions_consumptions.created_at >= " . $interval;
                $finals_to_warehouse_statistics .= " and products_finals_to_warehouses.created_at >= " . $interval;
            }

            $purchases_statistics = \DB::select($purchases_statistics);
            $consumptions_statistics = \DB::select($consumptions_statistics);
            $finals_to_warehouse_statistics = \DB::select($finals_to_warehouse_statistics);

            return response()->json([
                'purchases_statistics' => $purchases_statistics[0]->quantity,
                'consumptions_statistics' => $consumptions_statistics[0]->quantity,
                'finals_to_warehouse_statistics' => $finals_to_warehouse_statistics[0]->quantity
            ]);

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

    public function get_purchases_data_chart(Request $request)
    {
        try{  
            $purchases_charts =  \DB::select("SELECT DATE_FORMAT( purchases_orders.created_at, '%c' ) AS MONTH,
            SUM(purchases_orders.total_load) AS total FROM purchases_orders 
            WHERE purchases_orders.state_id = 2 AND
            DATE_FORMAT( purchases_orders.created_at, '%Y' ) = DATE_FORMAT(NOW(), '%Y')
            GROUP BY MONTH( purchases_orders.created_at )");

            $purchases_charts = \collect($purchases_charts);
            
            $dataset = [
                'label' => 'Ingreso',
                'backgroundColor' => 'green',
                'data' => null
            ];

            $data = array();

            for($i=1; $i<=12; $i++) {
                $item = $purchases_charts->where('MONTH', $i);
                
                if(  $item->isEmpty() )
                    array_push($data, 0);
                else
                    array_push($data, floatval( $item->sole()->total ) );
                
            }
    
            $dataset['data'] = $data;

            return response()->json($dataset);

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


    public function get_prd_final_data_chart(Request $request)
    {
        try{

            $products_final_data =  \DB::select("SELECT DATE_FORMAT( products_finals_to_warehouses.created_at, '%c' ) AS MONTH,
            SUM(products_finals_to_warehouses.quantity) AS total FROM products_finals_to_warehouses 
            WHERE products_finals_to_warehouses.state_id = 2 AND
            DATE_FORMAT( products_finals_to_warehouses.created_at, '%Y' ) = DATE_FORMAT(NOW(), '%Y')
            GROUP BY MONTH(products_finals_to_warehouses.created_at)");

            $products_final_data = \collect($products_final_data);
            
            $dataset = [
                'label' => 'Ingresos',
                'backgroundColor' => 'blue',
                'data' => null
            ];

            $data = array();

            for($i=1; $i<=12; $i++) {
                $item = $products_final_data->where('MONTH', $i);
                
                if(  $item->isEmpty() )
                    array_push($data, 0);
                else
                    array_push($data, floatval( $item->sole()->total ) );
                
            }
    
            $dataset['data'] = $data;

            return response()->json($dataset);

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


    public function get_consumption_data_chart(Request $request)
    {
        try{

            $consumption_charts =  \DB::select("SELECT DATE_FORMAT( productions_consumptions.created_at, '%c' ) AS MONTH,
            SUM(productions_consumptions.consumption_production) AS total FROM productions_consumptions
            INNER JOIN productions_orders on productions_orders.id = productions_consumptions.production_order_id
            WHERE productions_orders.state_id = 2 AND
            DATE_FORMAT( productions_consumptions.created_at, '%Y' ) = DATE_FORMAT(NOW(), '%Y')
            GROUP BY MONTH(productions_consumptions.created_at)");

            $consumption_charts = \collect($consumption_charts);
            
            $dataset = [
                'label' => 'Ingreso',
                'backgroundColor' => 'red',
                'data' => null
            ];

            $data = array();

            for($i=1; $i<=12; $i++) {
                $item = $consumption_charts->where('MONTH', $i);
                
                if(  $item->isEmpty() )
                    array_push($data, 0);
                else{
                    array_push($data, floatval( $item->sole()->total ) );
                
                }
                
            }
    
            $dataset['data'] = $data;

            return response()->json($dataset);

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
