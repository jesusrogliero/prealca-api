<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'provider_id',
        'total_products',
        'total_load',
        'observations',
        'nro_sada_guide',
        "total_nonconforming"
    ];
}
