<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuppliesMinorsNoconform extends Model
{
    use HasFactory;

    protected $fillable = ['supplie_minor_id', 'quantity', 'observation'];
}
