<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookChapter extends Model
{
    protected $fillable = [

        'book_id',
        'chapter_order',
        'title',
        'content'

    ];

    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }
}