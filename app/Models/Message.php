<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = [
        'text',
        'media',
        'sender_id',
        'receiver_id'
    ];


    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(){
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function adsJob(){
        return $this->belongsTo(AdsJobs::class, 'ads_jobs_id');
    }

    public function medias(){
        return $this->hasMany(Media::class);
    }
}
