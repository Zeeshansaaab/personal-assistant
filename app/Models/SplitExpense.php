<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SplitExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'paid_by',
        'amount',
        'description',
        'expense_date',
        'split_type',
        'split_details',
        'is_settled',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'split_details' => 'array',
        'is_settled' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getAmountOwedBy($userId)
    {
        if ($this->split_type === 'equal') {
            $memberCount = $this->group->members()->count();
            return $memberCount > 0 ? $this->amount / $memberCount : 0;
        }

        if ($this->split_type === 'unequal' && $this->split_details) {
            return $this->split_details[$userId] ?? 0;
        }

        return 0;
    }
}

