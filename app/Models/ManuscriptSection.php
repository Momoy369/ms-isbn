<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManuscriptSection extends Model
{
    protected $fillable = [

        'book_id',

        'section_type',

        'content'

    ];

    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }
}