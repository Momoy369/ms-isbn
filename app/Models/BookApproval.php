<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookApproval extends Model
{
    protected $fillable = [

        'book_id',

        'approval_type',

        'approved_by',

        'approved_at'

    ];

    public function book()
    {
        return $this->belongsTo(
            Book::class
        );
    }
}