<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'quantity_after',
        'quantity_before',
        'quantity',
        'module',
        'observation'
    ];
}
