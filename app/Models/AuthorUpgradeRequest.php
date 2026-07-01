<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorUpgradeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'checklist',
        'request_note',
        'supporting_document_path',
        'submitted_at',
        'reviewed_at',
        'reviewed_by_user_id',
        'review_notes',
    ];

    protected $casts = [
        'checklist' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
