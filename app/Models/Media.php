<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    protected $fillable = [
        'url',
    ];
    protected $table = 'medias';
    public function message(){
        return $this->belongsTo(Message::class, 'message_id');
    }
}
