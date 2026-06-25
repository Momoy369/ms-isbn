<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreCatalogItem extends Model
{
    protected $fillable = [
        'book_id',
        'legacy_book_id',
        'slug',
        'title',
        'subtitle',
        'author_name',
        'product_type',
        'description',
        'list_price',
        'promo_price',
        'stock',
        'is_active',
        'is_featured',
        'sort_order',
        'cover_image_path',
        'ebook_read_link',
        'admin_notes',
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function isEbook(): bool
    {
        return $this->product_type === 'ebook';
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function legacyBook()
    {
        return $this->belongsTo(LegacyBook::class);
    }

    public function orders()
    {
        return $this->hasMany(StoreOrder::class);
    }

    public function finalPrice(): float
    {
        $promo = (float) ($this->promo_price ?? 0);
        if ($promo > 0 && $promo < (float) $this->list_price) {
            return $promo;
        }

        return (float) $this->list_price;
    }

    public function hasStock(): bool
    {
        if ($this->stock === null) {
            return true;
        }

        return (int) $this->stock > 0;
    }
}
