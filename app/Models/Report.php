<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table="reports";
    use HasFactory;
    protected $fillable = [
        'title',
        "content"
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function adJob()
    {
        return $this->belongsTo(AdsJobs::class, "ads_jobs_id");
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
