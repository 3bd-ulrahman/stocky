<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'name',
        'qty',
        'cost',
        'price',
        'code',
        'image'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'qty' => 'double',
        'cost' => 'double',
        'price' => 'double',
    ];
}
