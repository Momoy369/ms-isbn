<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class StoreVoucher extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'minimum_subtotal',
        'max_discount_amount',
        'applies_to',
        'usage_limit',
        'used_count',
        'start_at',
        'end_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'minimum_subtotal' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(StoreOrder::class, 'voucher_id');
    }

    public function isCurrentlyActive(): bool
    {
        $now = now();

        if (!$this->is_active) {
            return false;
        }

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function appliesToItem(StoreCatalogItem $item): bool
    {
        return match ((string) $this->applies_to) {
            'print' => $item->isPrint(),
            'ebook' => $item->isEbook(),
            'print_ebook' => $item->hasSeparateFormats(),
            default => true,
        };
    }

    public function canApplyToSubtotal(float $subtotal): bool
    {
        if ($this->minimum_subtotal === null) {
            return true;
        }

        return $subtotal >= (float) $this->minimum_subtotal;
    }

    public function calculateDiscount(float $subtotal): float
    {
        $subtotal = max(0, $subtotal);

        if (!$this->canApplyToSubtotal($subtotal)) {
            return 0.0;
        }

        $discount = 0.0;
        if ($this->discount_type === 'fixed') {
            $discount = (float) $this->discount_value;
        } else {
            $discount = $subtotal * ((float) $this->discount_value / 100);
        }

        if ($this->max_discount_amount !== null && (float) $this->max_discount_amount > 0) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return max(0.0, min($subtotal, $discount));
    }
}
