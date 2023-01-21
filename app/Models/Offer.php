<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'ads_jobs_id',
        'status'
    ];

    public function adsJob(){
        return $this->belongsTo(AdsJobs::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
