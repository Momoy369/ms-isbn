<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookClaimRequest extends Model
{
    protected $fillable = [
        'book_id',
        'user_id',
        'ktp_number',
        'author_name',
        'status',
        'notes',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
