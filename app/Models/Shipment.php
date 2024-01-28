<?php

namespace App\Models;

use App\Models\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $table = 'shipments';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'sale_id',
        'user_id',
        'date',
        'Ref',
        'delivered_to',
        'shipping_address',
        'status',
        'shipping_details',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'sale_id' => 'integer',
        'status' => ShipmentStatus::class
    ];


    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Sale::class,
            'id', // Foreign key on the Sale model
            'id', // Foreign key on the User model
            'sale_id', // Local key on the Shipment model
            'user_id' // Local key on the Sale model
        );
    }

    public function representative()
    {
        return $this->hasOneThrough(
            User::class,
            Sale::class,
            'id', // Foreign key on the Sale model
            'id', // Foreign key on the User model
            'sale_id', // Local key on the Shipment model
            'representative_id' // Local key on the Sale model
        );
    }
}
