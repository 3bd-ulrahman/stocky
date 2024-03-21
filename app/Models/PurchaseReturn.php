<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use SoftDeletes;

    protected $table = 'purchase_returns';

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'purchase_id',
        'provider_id',
        'date',
        'Ref',
        'GrandTotal',
        'discount',
        'shipping',
        'notes',
        'TaxNet',
        'tax_rate',
        'statut',
        'paid_amount',
        'payment_statut',
    ];

    protected $casts = [
        'GrandTotal' => 'double',
        'user_id' => 'integer',
        'purchase_id' => 'integer',
        'provider_id' => 'integer',
        'warehouse_id' => 'integer',
        'discount' => 'double',
        'shipping' => 'double',
        'TaxNet' => 'double',
        'tax_rate' => 'double',
        'paid_amount' => 'double',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany('App\Models\PurchaseReturnDetails');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function provider()
    {
        return $this->belongsTo('App\Models\Provider');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase');
    }

    public function facture()
    {
        return $this->hasMany('App\Models\PaymentPurchaseReturns');
    }

}
