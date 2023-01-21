<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'educations';
    use HasFactory;
    protected $fillable = [
        'university',
        'specialization',
        'level',
        'date_in',
        'date_to',
        'mark',
        'country'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
