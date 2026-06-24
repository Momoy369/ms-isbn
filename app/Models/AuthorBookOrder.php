<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorBookOrder extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'publishing_package_id',
        'print_price_rule_id',
        'author_invoice_id',
        'order_type',
        'title',
        'pages',
        'quantity',
        'unit_price',
        'subtotal',
        'shipping_cost',
        'total_amount',
        'destination_province',
        'destination_city',
        'destination_city_id',
        'postal_code',
        'shipping_address',
        'courier',
        'courier_service',
        'etd',
        'shipping_payload',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_payload' => 'array',
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

    public function package()
    {
        return $this->belongsTo(PublishingPackage::class, 'publishing_package_id');
    }

    public function printPriceRule()
    {
        return $this->belongsTo(PrintPriceRule::class);
    }

    public function invoice()
    {
        return $this->belongsTo(AuthorInvoice::class, 'author_invoice_id');
    }
}
