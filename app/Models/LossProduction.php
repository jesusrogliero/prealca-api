<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'consumption_id',
        'packing_area',
        'lab',
        'hopper_auger',
        'total_recovered'
    ];
}
