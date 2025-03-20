<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    use HasFactory;
    protected $fillable = [
        'application_id',
        'employer_id',
        'job_seeker_id',
        'status',
        'text',
        'scheduled_at',
        'reminder_sent',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    public function jobSeeker()
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }
    public function application(){
        return $this->belongsTo(Application::class);
    }

}
