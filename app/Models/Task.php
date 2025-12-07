<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'repeat_type',
        'daily_reminder',
        'weekend_reminder',
        'is_completed',
        'completed_at',
        'user_id'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'daily_reminder' => 'boolean',
        'weekend_reminder' => 'boolean',
        'is_completed' => 'boolean',
    ];
}

