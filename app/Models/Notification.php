<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = [
        'content',
        'user_id',
        'ads_jobs_id'
    ];

    public function adjobs(){
        return $this->belongsTo(AdsJobs::class, 'ads_jobs_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function report(){
        return $this->belongsTo(Report::class, 'report_id');
    }
}
