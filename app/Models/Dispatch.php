<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiver_id',
        'state_id',
        'sica_code',
        'guide_sada',
        'unid',
        'total',
        'observation',
        'drive_name',
        'drive_identity'
    ];
}
