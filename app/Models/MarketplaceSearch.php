<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceSearch extends Model
{
    protected $fillable = [
        'search',
        'status',
        'products',
        'error',
    ];

    protected $casts = [
        'products' => 'array',
    ];
}