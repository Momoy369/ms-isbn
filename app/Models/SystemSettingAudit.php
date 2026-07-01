<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSettingAudit extends Model
{
    protected $fillable = [
        'key',
        'old_value',
        'new_value',
        'changed_by',
        'is_sensitive',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
    ];
}
