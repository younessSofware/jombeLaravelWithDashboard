<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $table = 'plans';
    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'period',
        'period_en',
        'price'
    ];
}
