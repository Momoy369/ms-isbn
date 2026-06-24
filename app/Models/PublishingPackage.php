<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishingPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'includes_editing',
        'includes_layout',
        'includes_cover_design',
        'price',
    ];

    protected $casts = [
        'includes_editing' => 'boolean',
        'includes_layout' => 'boolean',
        'includes_cover_design' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'publishing_package_id');
    }
}
