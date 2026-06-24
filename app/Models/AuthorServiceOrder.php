<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorServiceOrder extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'additional_service_id',
        'author_invoice_id',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function service()
    {
        return $this->belongsTo(AdditionalService::class, 'additional_service_id');
    }

    public function invoice()
    {
        return $this->belongsTo(AuthorInvoice::class, 'author_invoice_id');
    }
}
