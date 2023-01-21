<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;
    protected $fillable = [
        'enterprise',
        'job',
        'field',
        'date_in',
        'date_to',
        'desc_job',
        'country'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
