<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionsOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'formula_id',
        'products_final_id',
        'quantity',
        'state_id',
        'issued_by'
    ];
}
