<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookPackageItem extends Model
{
    protected $table = 'book_package_items';

    protected $fillable = [
        'book_id',
        'publishing_package_item_id',
        'name',
        'assigned_to_role',
        'is_required',
        'is_completed',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function packageItem()
    {
        return $this->belongsTo(PublishingPackageItem::class, 'publishing_package_item_id');
    }
}
