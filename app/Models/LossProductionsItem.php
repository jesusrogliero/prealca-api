<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossProductionsItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'loss_production_id',
        'primary_product_id',
        'loss_quantity',
        'mixing_area_l1',
        'mixing_area_l2',
        'total'
    ];
}
