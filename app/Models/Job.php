<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /** @use HasFactory<\Database\Factories\JobFactory> */
    use HasFactory;

    public static array $experiences = ['entry','intermediate','senior'];
    public static array $categories = [
        'IT',
        'Design',
        'Sales',
        'Software',
        'Finance',
        'Marketing',
    ];
}
