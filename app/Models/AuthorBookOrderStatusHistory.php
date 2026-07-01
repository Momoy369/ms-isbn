<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorBookOrderStatusHistory extends Model
{
    protected $fillable = [
        'author_book_order_id',
        'changed_by_user_id',
        'context',
        'from_status',
        'to_status',
        'note',
    ];

    public function order()
    {
        return $this->belongsTo(AuthorBookOrder::class, 'author_book_order_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
