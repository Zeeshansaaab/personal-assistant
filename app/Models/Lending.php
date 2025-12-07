<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lending extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_name',
        'item_type',
        'amount',
        'item_description',
        'date_given',
        'expected_return_date',
        'actual_return_date',
        'is_returned',
        'notes',
        'user_id'
    ];

    protected $casts = [
        'date_given' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'amount' => 'decimal:2',
        'is_returned' => 'boolean',
    ];
}

