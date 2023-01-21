<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'invoice_status',
        'payment_id',
        'item_name',
        'quantity',
        'Result'
    ];
    
    
    public function user(){
         return $this->belongsTo(User::class);
    }
    
    public function adJob(){
         return $this->belongsTo(AdsJobs::class, 'ads_jobs_id');
    }
    
    public function notifications(){
        return $this->hasMany(Notification::class);
    }
}
