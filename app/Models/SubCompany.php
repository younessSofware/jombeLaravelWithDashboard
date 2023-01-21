<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCompany extends Model
{
    protected $table = 'subcompanies';
    use HasFactory;
    protected $fillable = [
        'company_id',
        'country',
        'city',
        'employees'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
