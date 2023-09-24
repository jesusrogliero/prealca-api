<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuppliesMinor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'stock', 'consumption_weight_package', 'unid'];
}
