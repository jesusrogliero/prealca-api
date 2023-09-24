<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

# Grupo de rutas para la autenticacion
Route::group( ['prefix' => "auth"], function() {
	Route::post('login', 'AuthController@login');
	Route::post('signup', 'AuthController@signup');
});


# rutas para usuarios autenticados
Route::group(['middleware' => ['auth:api']], function () {

	Route::resource('users', 'UsersController');

	Route::get('get_session', 'AuthController@getSession');
	Route::get('get_user', 'AuthController@getUser');
	
	Route::resource('employes', 'EmployesController');
	Route::get('get_provinces', 'EmployesController@show_provinces');
	Route::get('get_positions', 'EmployesController@show_positions');
	Route::get('get_cities_of_provinces/{province_id}', 'EmployesController@show_cities_of_provinces');

	Route::resource('militiamen', 'MilitiamenController');

	Route::resource('machineries_consumables', 'MachineriesConsumablesController');
	Route::resource('cleaning_Supplies', 'CleaningSuppliesController');
	Route::get('presentations', 'CleaningSuppliesController@show_presentations');
	Route::resource('cleaning_tools', 'CleaningToolsController');

	Route::get('primaries_products_histories', 'PrimariesProductsHistories@index');
	
	Route::get('transactions', 'TransactionsController@index');

	Route::resource("users", "UsersController");
	
	Route::resource("types_identities", "TypesIdentitiesController");

//---------------------------------------------------------------------------------------------------------------

	// Administracion
	Route::resource('providers', 'ProvidersController');
	Route::resource('receivers', 'ReceiversController');

	// Almacen
	Route::resource('products_finals', 'ProductsFinalsController')->only(['index', 'store', 'show']);
	Route::get('get_products_finals', 'ProductsFinalsController@get_products_finals');

	Route::resource('nonconforming_products', 'NonconformingProductsController')->only(['index', 'store', 'show', 'update']);

	Route::resource('primaries_products', 'PrimariesProductsController');
	Route::get('get_all_primaries_products', 'PrimariesProductsController@get_all_primaries_products');

	Route::resource('supplies_minors', 'SuppliesMinorsController');
	Route::resource('supplies_minors_noconform', 'SuppliesMinorsNoconformController')->only(['index', 'store', 'show']);

	Route::resource('purchases_orders', 'PurchasesOrderController');
	Route::get('get_purchase_order_details/{id}', 'PurchasesOrderController@get_details');
	Route::get('approve_purchase/{id}', 'PurchasesOrderController@approve');
	Route::post('set_observation/{id}', 'PurchasesOrderController@set_observation');

	Route::get('get_purchases_orders_items/{id}', 'PurchasesOrdersItemsController@index');
	Route::resource('purchases_orders_items', 'PurchasesOrdersItemsController')->except('index');

	Route::resource('products_finals_to_warehouses', 'ProductsFinalsToWarehousesController')->only(['index', 'show', 'update']);
	Route::get('enter_products_finals_to_warehouses/{order_id}', 'ProductsFinalsToWarehousesController@enter_inventory');


	// Produccion
	Route::resource('formulas', 'FormulasController');
	Route::get('get_formula_items/{formula_id}', 'FormulasItemsController@get_items');
	Route::resource('formulas_items', 'FormulasItemsController')->except('index');

	Route::resource('lines_productions', 'LinesController');

	Route::resource('productions_orders', 'ProductionsOrdersController');
	Route::get('get_formula_with_production_order/{production_order_id}', 'ProductionsOrdersController@get_formula_with_production_order');

	Route::resource('productions_consumptions', 'ProductionsConsumptionsController')->only(['store', 'show']);
	Route::post('approve_order/', 'ProductionsConsumptionsController@approve');

	Route::resource('productions_consumptions_items','ProductionsConsumptionsItemsController')->only(['show', 'update']);
	Route::get('get_consumption_items/{consumption_id}', 'ProductionsConsumptionsItemsController@get_consumption_items');
    Route::get('get_consumptions_details/{production_order_id}', 'ProductionsConsumptionsController@get_consumptions_details');


	Route::resource('consumptions_supplies_minors', 'ConsumptionsSuppliesMinorsController')->only(['index', 'store', 'show']);

	Route::resource('loss_productions', 'LossProductionsController')->except('delete');
	Route::resource('loss_productions_items', 'LossProductionsItemsController')->only(['show', 'update']);
	Route::get('loss_production_items/{loss_production_id}', 'LossProductionsItemsController@get_items' );

	Route::resource('dispatches', 'DispatchesController');
	Route::post('set_dispatch_observation/{id}', 'DispatchesController@set_observation');
	Route::get('get_dispatch_details/{id}', 'DispatchesController@get_details');
	Route::get('approve_dispatch/{id}', 'DispatchesController@approve');

	Route::resource('dispatches_items', 'DispatchesItemsController')->except('index');
	Route::get('dispatch_items/{dispatch_id}', 'DispatchesItemsController@index');



	// Estadisticas
	Route::post('get_statistics', 'StatisticsController@get_statistics');
	Route::get('get_purchases_data_chart', 'StatisticsController@get_purchases_data_chart');
	Route::get('get_prd_final_data_chart', 'StatisticsController@get_prd_final_data_chart');
	Route::get('get_consumption_data_chart', 'StatisticsController@get_consumption_data_chart');
	
	
});
