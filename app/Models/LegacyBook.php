<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyBook extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'author_name',
        'author_user_id',
        'isbn',
        'published_year',
        'list_price',
        'royalty_enabled',
        'royalty_rate',
        'distribution_online',
        'distribution_ebook',
        'distribution_marketplace',
        'status',
        'notes',
    ];

    protected $casts = [
        'published_year' => 'integer',
        'list_price' => 'decimal:2',
        'royalty_enabled' => 'boolean',
        'royalty_rate' => 'decimal:4',
        'distribution_online' => 'boolean',
        'distribution_ebook' => 'boolean',
        'distribution_marketplace' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function externalSales()
    {
        return $this->hasMany(ExternalSalesRecord::class, 'legacy_book_id');
    }

    public function royaltyRate(): float
    {
        $rate = (float) ($this->royalty_rate ?? 0);

        if ($rate <= 0) {
            return 0.20;
        }

        return min($rate, 1.00);
    }
}
