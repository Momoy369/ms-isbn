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
        'ebook_price',
        'ebook_promo_price',
        'admin_notes',
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'ebook_price' => 'decimal:2',
        'ebook_promo_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function isEbook(): bool
    {
        return in_array((string) $this->product_type, ['ebook', 'print_ebook'], true);
    }

    public function isPrint(): bool
    {
        return in_array((string) $this->product_type, ['print', 'print_ebook'], true);
    }

    public function productTypeLabel(): string
    {
        return match ((string) $this->product_type) {
            'ebook' => 'EBOOK',
            'print_ebook' => 'PRINT + EBOOK',
            default => 'PRINT',
        };
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

    /**
     * Harga final untuk format print (atau harga default jika bukan print_ebook).
     */
    public function finalPrice(): float
    {
        $promo = (float) ($this->promo_price ?? 0);
        if ($promo > 0 && $promo < (float) $this->list_price) {
            return $promo;
        }

        return (float) $this->list_price;
    }

    /**
     * Harga final untuk format ebook. Fallback ke harga print jika belum diset.
     */
    public function finalEbookPrice(): float
    {
        $base = (float) ($this->ebook_price ?? 0);
        if ($base <= 0) {
            return $this->finalPrice();
        }

        $promo = (float) ($this->ebook_promo_price ?? 0);
        if ($promo > 0 && $promo < $base) {
            return $promo;
        }

        return $base;
    }

    /**
     * Harga final berdasarkan format yang dipilih customer.
     */
    public function finalPriceForFormat(string $format): float
    {
        if ($format === 'ebook' && $this->product_type === 'print_ebook') {
            return $this->finalEbookPrice();
        }

        return $this->finalPrice();
    }

    /**
     * Apakah item ini memiliki format yang bisa dipilih secara terpisah.
     */
    public function hasSeparateFormats(): bool
    {
        return $this->product_type === 'print_ebook';
    }

    public function hasStock(): bool
    {
        if ($this->stock === null) {
            return true;
        }

        return (int) $this->stock > 0;
    }
}
