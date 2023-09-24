<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionsConsumption extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'total_production',
        'consumption_production',
        'nro_batch'
    ];
}
