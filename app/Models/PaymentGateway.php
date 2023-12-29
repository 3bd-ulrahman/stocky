<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $table = 'payment_gateways';

    protected $fillable = ['name', 'keys', 'is_active'];

    // Accessors & Mutators
    protected function keys(): Attribute
    {
        return new Attribute(
            get: fn ($keys) => json_decode($keys)
        );
    }
}
