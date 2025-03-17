<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'resume_path',
        'cover_letter',
        'status',
    ];

    protected $casts = [
        'status'=> 'string'
    ];

    public static array $statuses = ['applied','rejected','under_review','accepted'];
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function job(){
        return $this->belongsTo(Job::class,'job_id');
    }
}
