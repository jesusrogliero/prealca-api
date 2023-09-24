<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsFinalsToWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_final_id',
        'number_control',
        'date',
        'work_area',
        'origin',
        'destination',
        'quantity',
        'description',
        'guide_sunagro',
        'state_id',
        'production_order_id'
    ];
}
