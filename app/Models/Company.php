<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'employment',
        'date',
        'size',
        'notes'
    ];

    public function subcompanies()
    {
        return $this->hasMany(SubCompany::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
