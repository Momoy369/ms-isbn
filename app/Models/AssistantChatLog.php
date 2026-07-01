<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantChatLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question',
        'answer',
        'source',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
