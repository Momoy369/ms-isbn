<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'manuscript_title',
        'author_ktp_number',
        'service_order_ref',
        'marketing_ref',
        'source_channel',
        'status',
        'discount_scope',
        'subtotal',
        'discount_type',
        'discount_input',
        'discount_amount',
        'publishing_metadata',
        'total_amount',
        'notes',
        'created_by_user_id',
        'linked_user_id',
        'linked_book_id',
        'production_synced_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_input' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'publishing_metadata' => 'array',
        'total_amount' => 'decimal:2',
        'production_synced_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(PosOrderItem::class)->orderBy('id');
    }

    public function invoices()
    {
        return $this->hasMany(PosInvoice::class)->orderBy('installment_number');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function linkedBook()
    {
        return $this->belongsTo(Book::class, 'linked_book_id');
    }

    public function getPaidAmountAttribute(): float
    {
        if ($this->relationLoaded('invoices')) {
            return (float) $this->invoices
                ->where('status', 'paid')
                ->sum('amount');
        }

        return (float) $this->invoices()->where('status', 'paid')->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
