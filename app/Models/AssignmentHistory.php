<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentHistory extends Model
{
    protected $fillable = [

        'book_id',

        'role',

        'activity',

        'old_person',

        'new_person'

    ];

    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }
}