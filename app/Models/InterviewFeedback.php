<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewFeedback extends Model
{

    protected $fillable = [
        'interview_id',
        'employer_id',
        'feedback',
        'rating',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class,'interview_id');
    }
    public function employer()
    {
        return $this->belongsTo(User::class,'employer_id');
    }

}
