<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookFile extends Model
{
    protected $fillable = [

        'book_id',
        'type',
        'original_name',
        'note',
        'file_path',
        'mime_type',
        'file_size',
        'is_active'

    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function content()
    {
        return $this->hasOne(
            DocumentContent::class
        );
    }

    public function getAbsolutePath()
    {
        if (
            file_exists(
                $this->file_path
            )
        ) {

            return $this->file_path;
        }

        if (
            str_starts_with(
                $this->file_path,
                'generated/'
            )
        ) {

            return storage_path(
                'app/' .
                $this->file_path
            );
        }

        return storage_path(
            'app/public/' .
            $this->file_path
        );
    }
}