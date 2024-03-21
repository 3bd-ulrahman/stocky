<?php

namespace App\Models;

use App\Models\Enums\PurchaseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $table = 'purchases';

    protected $fillable = [
        'user_id',
        'provider_id',
        'warehouse_id',
        'date',
        'Ref',
        'GrandTotal',
        'discount',
        'shipping',
        'statut',
        'notes',
        'TaxNet',
        'tax_rate',
        'paid_amount',
        'payment_statut',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'provider_id' => 'integer',
        'warehouse_id' => 'integer',
        'GrandTotal' => 'double',
        'discount' => 'double',
        'shipping' => 'double',
        'TaxNet' => 'double',
        'tax_rate' => 'double',
        'paid_amount' => 'double',
        'status' => PurchaseStatus::class
    ];

    // Relationships
    public function details(): HasMany
    {
        return $this->hasMany('App\Models\PurchaseDetail');
    }

    public function facture(): HasMany
    {
        return $this->hasMany('App\Models\PaymentPurchase');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
