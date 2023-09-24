<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'lastname',
        'position_id',
        'cedula',
        'data_admission',
        'address',
        'city_id',
        'province_id',
        'nacionality',
        'phone',
        'genere',
        'date_brith'
    ];
}
