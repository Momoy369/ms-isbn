<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePackageConsultation extends Model
{
    protected $fillable = [
        'user_id',
        'publishing_package_id',
        'package_name',
        'package_base_price',
        'customer_name',
        'customer_phone',
        'customer_email',
        'manuscript_title',
        'manuscript_genre',
        'estimated_page_count',
        'target_publish_date',
        'budget_range',
        'selected_services',
        'estimated_total',
        'notes',
        'finance_notes',
        'next_action_at',
        'status',
        'source',
    ];

    protected $casts = [
        'selected_services' => 'array',
        'target_publish_date' => 'date',
        'next_action_at' => 'date',
        'package_base_price' => 'decimal:2',
        'estimated_total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(PublishingPackage::class, 'publishing_package_id');
    }
}
