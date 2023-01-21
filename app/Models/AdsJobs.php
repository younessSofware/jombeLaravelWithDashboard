<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsJobs extends Model
{
    protected $table = "adsjobs";
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'requirement',
        'yearsOfExperiences',
        'workTime'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function offers()
    {
        return $this->belongsToMany(User::class, 'offers')->withTimestamps();
    }

    public function saveds(){
        return $this->belongsToMany(User::class, 'savedjobs')->withTimestamps();
    }

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function notifications(){
        return $this->hasMany(Notification::class);
    }
    
    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function reports(){
        return $this->hasMany(Report::class);
    }
}
