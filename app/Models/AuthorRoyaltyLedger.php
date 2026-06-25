<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorRoyaltyLedger extends Model
{
    protected $fillable = [
        'author_user_id',
        'book_id',
        'period_start',
        'period_end',
        'gross_amount',
        'royalty_rate',
        'royalty_amount',
        'status',
        'payout_request_id',
        'generated_at',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_amount' => 'decimal:2',
        'royalty_rate' => 'decimal:4',
        'royalty_amount' => 'decimal:2',
        'generated_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function payoutRequest()
    {
        return $this->belongsTo(AuthorRoyaltyPayoutRequest::class, 'payout_request_id');
    }

    public function isAccrued(): bool
    {
        return $this->status === 'accrued';
    }
}
