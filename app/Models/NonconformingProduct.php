<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonconformingProduct extends Model
{
    use HasFactory;

    protected $fillable = ['primary_product_id', 'quantity', 'observation'];
}
