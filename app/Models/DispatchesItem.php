<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchesItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_final_id',
        'quantity',
        'dispatch_id'
    ];
}
