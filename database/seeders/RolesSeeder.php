<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role1 = Role::create(['name' => 'Admin']);
        $role2 = Role::create(['name' => 'Gerente']);
        $role3 = Role::create(['name' => 'Coordinador de Almacen']);
        $role4 = Role::create(['name' => 'Coordinador de Producción']);

        // Almacen------------------------------------------------------------------------------------------------------------

        // Permisos Materia Prima
        $permission = Permission::create(['name' => 'primaries_products.index'])->syncRoles([$role1, $role2, $role3, $role4]);
        $permission = Permission::create(['name' => 'primaries_products.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'primaries_products.show'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'primaries_products.update'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'primaries_products.destroy'])->syncRoles([$role1, $role2, $role3]);

        // Permisos Productos Finales
        $permission = Permission::create(['name' => 'product_final.index'])->syncRoles([$role1, $role2, $role3, $role4]);
        $permission = Permission::create(['name' => 'product_final.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'product_final.show'])->syncRoles([$role1, $role2, $role3]);

        // Permisos Insumos Menores
        $permission = Permission::create(['name' => 'supplies_minors.index'])->syncRoles([$role1, $role2, $role3, $role4]);
        $permission = Permission::create(['name' => 'supplies_minors.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'supplies_minors.show'])->syncRoles([$role1, $role2, $role3, $role4]);
        $permission = Permission::create(['name' => 'supplies_minors.update'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'supplies_minors.destroy'])->syncRoles([$role1, $role2, $role3]);

        // Permisos Insumos Menores
        $permission = Permission::create(['name' => 'supplies_minors_noconform.index'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'supplies_minors_noconform.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'supplies_minors_noconform.show'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'supplies_minors_noconform.update'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'supplies_minors_noconform.destroy'])->syncRoles([$role1, $role2, $role3]);

        
        // Permisos Materia Prima no Conforme
        $permission = Permission::create(['name' => 'nonconforming_products.index'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'nonconforming_products.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'nonconforming_products.show'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'nonconforming_products.update'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'nonconforming_products.destroy'])->syncRoles([$role1, $role2, $role3]);

         // Permisos Orden de Ingreso
         $permission = Permission::create(['name' => 'purchases_orders.index'])->syncRoles([$role1, $role2, $role3]);
         $permission = Permission::create(['name' => 'purchases_orders.store'])->syncRoles([$role1, $role2, $role3]);
         $permission = Permission::create(['name' => 'purchases_orders.show'])->syncRoles([$role1, $role2, $role3]);
         $permission = Permission::create(['name' => 'purchases_orders.update'])->syncRoles([$role1, $role2, $role3]);
         $permission = Permission::create(['name' => 'purchases_orders.destroy'])->syncRoles([$role1, $role2, $role3]);
         $permission = Permission::create(['name' => 'purchases_orders.approve_purchase'])->syncRoles([$role1, $role2, $role3]);
         $permission = Permission::create(['name' => 'purchases_orders.set_observation'])->syncRoles([$role1, $role2, $role3]);
         $permission = Permission::create(['name' => 'purchases_orders.get_details'])->syncRoles([$role1, $role2, $role3]);

                 
        // Permisos Items de la orden de ingreso
        $permission = Permission::create(['name' => 'purchases_orders_items.index'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'purchases_orders_items.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'purchases_orders_items.show'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'purchases_orders_items.update'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'purchases_orders_items.destroy'])->syncRoles([$role1, $role2, $role3]);

        // Permisos Orden despacho
        $permission = Permission::create(['name' => 'dispatch.index'])->syncRoles([$role1, $role2, $role3, $role4]);
        $permission = Permission::create(['name' => 'dispatch.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch.show'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch.update'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch.destroy'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch.approve'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch.get_details'])->syncRoles([$role1, $role2, $role3, $role4]);

        // Permisos Orden despacho
        $permission = Permission::create(['name' => 'dispatch_item.index'])->syncRoles([$role1, $role2, $role3, $role4]);
        $permission = Permission::create(['name' => 'dispatch_item.store'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch_item.show'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch_item.update'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'dispatch_item.destroy'])->syncRoles([$role1, $role2, $role3]);

        // Administracion-------------------------------------------------------------------------------------------------

        // Permisos Provedores
        $permission = Permission::create(['name' => 'providers.index'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'providers.store'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'providers.show'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'providers.update'])->syncRoles([$role1]);
        $permission = Permission::create(['name' => 'providers.destroy'])->syncRoles([$role1]);

        // Permisos Receptores
        $permission = Permission::create(['name' => 'receivers.index'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'receivers.store'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'receivers.show'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'receivers.update'])->syncRoles([$role1]);
        $permission = Permission::create(['name' => 'receivers.destroy'])->syncRoles([$role1]);

     
        // Produccion---------------------------------------------------------------------------------------------------


        // Permisos Lineas de Produccion
        $permission = Permission::create(['name' => 'lines.index'])->syncRoles([$role1, $role2, $role3, $role4]);
        $permission = Permission::create(['name' => 'lines.store'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'lines.show'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'lines.update'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'lines.destroy'])->syncRoles([$role1, $role2, $role4]);

        // Permisos Formulas
        $permission = Permission::create(['name' => 'formula.index'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula.store'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula.show'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula.update'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula.destroy'])->syncRoles([$role1, $role2, $role4]);

        // Permisos Formulas Items
        $permission = Permission::create(['name' => 'formula_item.index'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula_item.store'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula_item.show'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula_item.update'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'formula_item.destroy'])->syncRoles([$role1, $role2, $role4]);


        // Permisos Orden de Produccion
        $permission = Permission::create(['name' => 'productions_orders.index'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'productions_orders.store'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'productions_orders.show'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'productions_orders.update'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'productions_orders.destroy'])->syncRoles([$role1, $role2]);
        $permission = Permission::create(['name' => 'productions_orders.get_formula_with_production_order'])->syncRoles([$role1, $role2, $role4]);

		// Permisos Orden consumo de produccion
		$permission = Permission::create(['name' => 'productions_consumptions.store'])->syncRoles([$role1, $role4]);
		$permission = Permission::create(['name' => 'productions_consumptions.show'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'productions_consumptions.approve_order'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'productions_consumptions.get_consumptions_details'])->syncRoles([$role1, $role2, $role4]);

		// Permisos Items consumo de produccion
		$permission = Permission::create(['name' => 'productions_consumptions_items.index'])->syncRoles([$role1, $role2, $role4]);
		$permission = Permission::create(['name' => 'productions_consumptions_items.store'])->syncRoles([$role1, $role4]);
		$permission = Permission::create(['name' => 'productions_consumptions_items.show'])->syncRoles([$role1, $role2, $role4]);
        
		// Permisos consumo insumo menores
		$permission = Permission::create(['name' => 'consumptions_supplies_minors.index'])->syncRoles([$role1, $role2, $role4]);
		$permission = Permission::create(['name' => 'consumptions_supplies_minors.store'])->syncRoles([$role1, $role4]);
		$permission = Permission::create(['name' => 'consumptions_supplies_minors.show'])->syncRoles([$role1, $role2, $role4]);

		// Permisos Merma de Produccion
		$permission = Permission::create(['name' => 'loss_productions.index'])->syncRoles([$role1, $role2, $role4]);
		$permission = Permission::create(['name' => 'loss_productions.store'])->syncRoles([$role1, $role4]);
		$permission = Permission::create(['name' => 'loss_productions.show'])->syncRoles([$role1, $role2, $role4]);
        $permission = Permission::create(['name' => 'loss_productions.update'])->syncRoles([$role1, $role2, $role4]);
        
		// Permisos Items de Merma de Produccion
		$permission = Permission::create(['name' => 'loss_productions_items.index'])->syncRoles([$role1, $role2, $role4]);
		$permission = Permission::create(['name' => 'loss_productions_items.show'])->syncRoles([$role1, $role2, $role4]);
		$permission = Permission::create(['name' => 'loss_productions_items.update'])->syncRoles([$role1, $role4]);

        // Permisos Ingreso producto final al almacen
		$permission = Permission::create(['name' => 'products_finals_to_warehouses.index'])->syncRoles([$role1, $role2, $role3]);
		$permission = Permission::create(['name' => 'products_finals_to_warehouses.enter_inventory'])->syncRoles([$role1, $role2, $role3]);
		$permission = Permission::create(['name' => 'products_finals_to_warehouses.show'])->syncRoles([$role1, $role2, $role3]);
        $permission = Permission::create(['name' => 'products_finals_to_warehouses.update'])->syncRoles([$role1, $role2, $role3]);
    }
}


/*
 public function __construct() {
        $this->middleware('can:product_final.index')->only('index');
        $this->middleware('can:product_final.store')->only('store');
        $this->middleware('can:product_final.show')->only('show');
        $this->middleware('can:product_final.update')->only('update');
        $this->middleware('can:product_final.destroy')->only('destroy');
    }
*/