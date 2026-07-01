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
        'manuscript_a4_pages',
        'manuscript_a5_pages',
        'a4_page_limit',
        'a5_page_limit',
        'over_limit_pages',
        'print_over_limit_pages',
        'layout_over_limit_fee',
        'editing_over_limit_fee',
        'print_over_limit_fee',
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
        'revision_requested_at',
        'print_started_at',
        'print_completed_at',
        'shipping_started_at',
        'shipped_at',
        'delivered_at',
        'tracking_number',
        'shipping_notes',
        'ebook_platform',
        'ebook_publication_link',
        'ebook_submitted_at',
        'ebook_published_at',
    ];

    protected $casts = [
        'manuscript_a4_pages' => 'integer',
        'manuscript_a5_pages' => 'integer',
        'a4_page_limit' => 'integer',
        'a5_page_limit' => 'integer',
        'over_limit_pages' => 'integer',
        'print_over_limit_pages' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'layout_over_limit_fee' => 'decimal:2',
        'editing_over_limit_fee' => 'decimal:2',
        'print_over_limit_fee' => 'decimal:2',
        'shipping_payload' => 'array',
        'paid_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'print_started_at' => 'datetime',
        'print_completed_at' => 'datetime',
        'shipping_started_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'ebook_submitted_at' => 'datetime',
        'ebook_published_at' => 'datetime',
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

    public function statusHistories()
    {
        return $this->hasMany(AuthorBookOrderStatusHistory::class)->latest();
    }
}
