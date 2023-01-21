<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'fullname',
        'photo',
        'dob',
        'phone',
        'country',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function subCompanies()
    {
        return $this->hasMany(SubCompany::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }
    
    public function experiences()
    {
         return $this->hasMany(Experience::class);
    }

    public function skills(){
        return $this->hasMany(Skill::class);
    }

    public function adsJobs(){
        return $this->hasMany(AdsJobs::class);
    }

    public function offers()
    {
        return $this->belongsToMany(AdsJobs::class, 'offers')->withTimestamps();
    }

    public function savedAds(){
        return $this->belongsToMany(AdsJobs::class, 'savedjobs')->withTimestamps();
    }

    public function messages()
    {
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
