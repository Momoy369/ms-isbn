<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookMessage extends Model
{
    protected $fillable = [

        'book_id',

        'user_id',

        'sender_name',

        'sender_role',

        'message',

        'attachment'

    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}