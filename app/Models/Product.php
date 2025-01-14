<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use App\Models\Enums\ProductType;
use App\Models\Translations\ProductTranslation;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements TranslatableContract
{
    use SoftDeletes, Translatable;

    protected $table = 'products';

    protected $fillable = [
        'code',
        'Type_barcode',
        'cost',
        'price',
        'unit_id',
        'unit_sale_id',
        'unit_purchase_id',
        'stock_alert',
        'category_id',
        'sub_category_id',
        'is_variant',
        'is_imei',
        'tax_method',
        'image',
        'brand_id',
        'is_active',
        'note',
        'type'
    ];

    public $translatedAttributes = ['name'];

    public $translationModel = ProductTranslation::class;

    protected $with = ['translations'];

    protected $casts = [
        'type' => ProductType::class,
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
        'unit_id' => 'integer',
        'unit_sale_id' => 'integer',
        'unit_purchase_id' => 'integer',
        'is_variant' => 'integer',
        'is_imei' => 'integer',
        'brand_id' => 'integer',
        'is_active' => 'integer',
        'cost' => 'double',
        'price' => 'double',
        'stock_alert' => 'double',
        'TaxNet' => 'double'
    ];

    // Relationships
    public function PurchaseDetail(): BelongsTo
    {
        return $this->belongsTo('App\Models\PurchaseDetail');
    }

    public function SaleDetail()
    {
        return $this->belongsTo('App\Models\SaleDetail');
    }

    public function QuotationDetail()
    {
        return $this->belongsTo('App\Models\QuotationDetail');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function unitPurchase()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_purchase_id');
    }

    public function unitSale()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_sale_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class)->withDefault([
            'name' => 'N/D'
        ]);
    }

    public function productVariant(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')->withPivot('qte');
    }
}
