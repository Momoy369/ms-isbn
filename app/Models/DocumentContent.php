<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentContent extends Model
{
    protected $fillable = [

        'book_file_id',

        'content'

    ];

    public function file()
    {
        return $this->belongsTo(
            BookFile::class
        );
    }
}