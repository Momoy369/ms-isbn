<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorRoyaltyPayoutRequest extends Model
{
    protected $fillable = [
        'author_user_id',
        'amount',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'bank_branch',
        'requested_at',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
