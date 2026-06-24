<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditResult extends Model
{
    protected $fillable = [

        'book_id',

        'rule',

        'passed',

        'message'

    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}