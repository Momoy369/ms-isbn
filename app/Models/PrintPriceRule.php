<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintPriceRule extends Model
{
    protected $fillable = [
        'name',
        'paper_type',
        'paper_size',
        'print_type',
        'min_pages',
        'max_pages',
        'base_price',
        'price_per_page',
        'weight_per_copy_gram',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_per_page' => 'decimal:2',
        'is_active' => 'boolean',
        'min_pages' => 'integer',
        'max_pages' => 'integer',
        'weight_per_copy_gram' => 'integer',
    ];

    public function calculateUnitPrice(int $pages): float
    {
        return (float) $this->base_price + ((float) $this->price_per_page * max(0, $pages));
    }

    public function calculateWeight(int $quantity): int
    {
        return max(100, $this->weight_per_copy_gram * max(1, $quantity));
    }
}
