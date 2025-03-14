<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /** @use HasFactory<\Database\Factories\JobFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'experience',
        'type',
        'status',
        'user_id',
        'catogry',
        'salary',
        'location',
        'application_deadline',
    ];

    protected $casts = [
        'application_deadline' => 'date',
    ];

    public static array $experiences = ['entry','intermediate','senior'];
    public static array $categories = [
        'IT',
        'Design',
        'Sales',
        'Software',
        'Finance',
        'Marketing',
    ];
    public static array $type=['full_time','part_time','contract','internship'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
