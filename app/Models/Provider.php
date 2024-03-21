<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;

    protected $table = 'providers';

    protected $fillable = [
        'name',
        'code',
        'adresse',
        'phone',
        'country',
        'email',
        'city',
        'tax_number'
    ];

    protected $casts = [
        'code' => 'integer',
    ];

    // Relationships
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'provider_id', 'id');
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class, 'provider_id', 'id');
    }
}
