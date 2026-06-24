<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublishingPackageItem extends Model
{
    protected $fillable = [
        'publishing_package_id',
        'name',
        'description',
        'assigned_to_role',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(PublishingPackage::class, 'publishing_package_id');
    }

    public function bookItems()
    {
        return $this->hasMany(BookPackageItem::class, 'publishing_package_item_id');
    }
}
