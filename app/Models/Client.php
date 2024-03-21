<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'name',
        'code',
        'adresse',
        'email',
        'phone',
        'country',
        'city',
        'tax_number'
    ];

    protected $casts = [
        'code' => 'integer',
    ];

    // Relationships
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'client_id');
    }

    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class, 'client_id');
    }
}
