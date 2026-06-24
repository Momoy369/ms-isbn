<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalSalesRecord extends Model
{
    protected $fillable = [
        'book_id',
        'input_by_user_id',
        'channel',
        'format',
        'quantity',
        'unit_price',
        'gross_amount',
        'sold_at',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'sold_at' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by_user_id');
    }
}
