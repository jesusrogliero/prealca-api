<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumptionsSuppliesMinor extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_minor_id',
        'consumption_id',
        'number_packages',
        'consumption',
        'consumption_bags',
        'envoplast_consumption'
    ];
}
