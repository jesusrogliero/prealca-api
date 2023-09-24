<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasesOrdersItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'primary_product_id',
        'quantity',
        'due_date',
        'purchase_order_id',
        'nro_lote',
        'nonconform_quantity',
    ];
}
