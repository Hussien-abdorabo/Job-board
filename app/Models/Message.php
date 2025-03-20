<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Message extends Model
{
    use HasApiTokens;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'application_id',
        'content',
    ];
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');

    }
    public function receiver(){
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function application(){
        return $this->belongsTo(Application::class, 'application_id');
    }
}
