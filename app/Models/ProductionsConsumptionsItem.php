<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionsConsumptionsItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_consumption_id',
        'primary_product_id',
        'to_mixer',
        'remainder1',
        'remainder2',
        'consumption_production',
        'consumption_percentage',
        'theoretical_consumption'
    ];
}
