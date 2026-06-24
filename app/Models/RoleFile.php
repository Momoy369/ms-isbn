<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleFile extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'role',
        'category',
        'title',
        'description',
        'disk',
        'file_path',
        'mime_type',
        'file_size',
        'is_image',
        'share_token',
        'share_expires_at',
    ];

    protected $casts = [
        'book_id' => 'integer',
        'file_size' => 'integer',
        'is_image' => 'boolean',
        'share_expires_at' => 'datetime',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
