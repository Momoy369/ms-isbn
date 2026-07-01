<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalBoardCard extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'board_column',
        'priority',
        'due_at',
        'card_order',
        'color',
        'is_archived',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
