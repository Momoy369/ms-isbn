<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishingPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'supports_print',
        'supports_ebook',
        'includes_editing',
        'includes_layout',
        'includes_cover_design',
        'includes_author_certificate',
        'includes_google_scholar',
        'requires_hki_registration',
        'default_print_quantity',
        'price',
    ];

    protected $casts = [
        'supports_print' => 'boolean',
        'supports_ebook' => 'boolean',
        'includes_editing' => 'boolean',
        'includes_layout' => 'boolean',
        'includes_cover_design' => 'boolean',
        'includes_author_certificate' => 'boolean',
        'includes_google_scholar' => 'boolean',
        'requires_hki_registration' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'publishing_package_id');
    }

    public function items()
    {
        return $this->hasMany(PublishingPackageItem::class, 'publishing_package_id')->orderBy('sort_order');
    }
}
