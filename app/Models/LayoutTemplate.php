<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutTemplate extends Model
{
    protected $fillable = [

        'name',

        'paper_size',

        'font_family',

        'font_size',

        'margin_top',

        'margin_bottom',

        'margin_left',

        'margin_right',

        'is_active'

    ];

    public function books()
    {
        return $this->hasMany(
            Book::class
        );
    }
}
