<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'pos_order_id',
        'installment_number',
        'description',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_reference',
        'verified_by_user_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'installment_number' => 'integer',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $invoice): void {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        $prefix = 'INV-POS';
        $year = now()->format('Y');
        $month = now()->format('m');

        $lastNumber = self::where('invoice_number', 'like', "{$prefix}-{$year}{$month}%")
            ->lockForUpdate()
            ->count();

        $sequence = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'cancelled' => 'secondary',
            default => 'light',
        };
    }
}
