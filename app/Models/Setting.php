<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    protected $table = 'settings';

    protected $fillable = [
        'currency_id',
        'email',
        'CompanyName',
        'CompanyPhone',
        'CompanyAdress',
        'quotation_with_stock',
        'logo',
        'footer',
        'developed_by',
        'client_id',
        'warehouse_id',
        'default_language',
        'locales',
        'is_invoice_footer',
        'invoice_footer',
    ];

    protected $casts = [
        'currency_id' => 'integer',
        'client_id' => 'integer',
        'quotation_with_stock' => 'integer',
        'is_invoice_footer' => 'integer',
        'warehouse_id' => 'integer',
        'locales' => 'array'
    ];


    public function Currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }

    public function Client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}
