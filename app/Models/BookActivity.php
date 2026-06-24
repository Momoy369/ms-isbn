<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookActivity extends Model
{
    protected $fillable = [

        'book_id',

        'activity',

        'description'

    ];

    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }
}