<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookSection extends Model
{
    protected $fillable = [

        'book_id',
        'section_type',
        'title',
        'content',
        'sort_order',
        'heading_level'

    ];

    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }
}
